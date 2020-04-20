<?php

namespace Tests\Feature;

// use Mockery;
use App\Concert;
use Tests\TestCase;
use App\Facades\TicketCode;
use App\Billing\PaymentGateway;
use App\Billing\FakePaymentGateway;
use App\Mail\OrderConfirmationEmail;
use Illuminate\Support\Facades\Mail;
use App\Facades\OrderConfirmationNumber;
use App\OrderConfirmationNumberGenerator;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class PurchaseTicketsTest extends TestCase
{
    use DatabaseMigrations;

    protected function setUp()
    {
        parent::setUp();

        $this->paymentGateway = new FakePaymentGateway();
        app()->instance(PaymentGateway::class, $this->paymentGateway);
        Mail::fake();
    }

    private function orderTickets($concert, $params)
    {
        return $this->json('POST', "/concerts/{$concert->id}/orders", $params);
    }

    /** @test */
    public function customer_can_purchase_published_concert_tickets()
    {
        $this->withoutExceptionHandling();

        // Create a concert
        $concert = factory(Concert::class)->state('published')->create(['ticket_price' => 3250])->addTickets(3);

        // $orderConfirmationNumberGenerator->generate();
        // $orderConfirmationNumberGenerator = \Mockery::mock(
        //     OrderConfirmationNumberGenerator::class,
        //     ['generate' => 'ORDERCONFIRMAION1234' ]
        // );
        // $this->app->instance(OrderConfirmationNumberGenerator::class,  $orderConfirmationNumberGenerator);

        OrderConfirmationNumber::shouldReceive('generate')->andReturn('ORDERCONFIRMAION1234');
        TicketCode::shouldReceive('generateFor')->andReturn('TICKETCODE1', 'TICKETCODE2', 'TICKETCODE3');
        // Act
        // Purchase concert tickets
        $res = $this->orderTickets($concert, [
            'email' => 'john@example.com',
            'ticket_quantity' => 3,
            'payment_token' => $this->paymentGateway->getValidTestToken(),
        ]);

        // Assert
        $res->assertStatus(201)
            ->assertJson([
                'confirmation_number' => 'ORDERCONFIRMAION1234',
                'email' => 'john@example.com',
                'ticket_quantity' => 3,
                'amount' => 9750,
                'tickets' => [
                    ['code' => 'TICKETCODE1'],
                    ['code' => 'TICKETCODE2'],
                    ['code' => 'TICKETCODE3'],
                ]
            ]);
        // Make sure the customer was charged the correct amount
        $this->assertEquals(9750, $this->paymentGateway->totalCharges());

        // Make sure that an order exists for this customer
        $order = $concert->orders()->where('email', 'john@example.com')->first();
        $this->assertNotNull($order);
        $this->assertEquals(3, $order->tickets()->count());


        $order = $concert->ordersFor('john@example.com')->first();
        $this->assertEquals(3, $order->ticketQuantity());
        Mail::assertSent(OrderConfirmationEmail::class, function ($mail) use ($order) {
            return $mail->hasTo('john@example.com')
                && $mail->order->id == $order->id;
        });
    }

    /** @test */
    public function cannot_purchase_tickets_to_an_unpublished_concert()
    {
        $concert = factory(Concert::class)->states('unpublished')->create();
        $concert->addTickets(100);
        $res = $this->orderTickets($concert, [
            'email' => 'john@example.com',
            'ticket_quantity' => 3,
            'payment_token' => $this->paymentGateway->getValidTestToken(),
        ]);


        $res->assertStatus(404);

        $this->assertEquals(0, $concert->orders()->count());
        // $this->paymentGateway
    }


    /** @test */
    public function cannot_purchase_tickets_when_another_custmer_is_tring_to_purchase()
    {
        $this->withoutExceptionHandling();
        $concert = factory(Concert::class)->states('published')->create(['ticket_price' => 12])->addTickets(5);

        // we are givinig a funciton what will be called by
        // paymentGateway before the charge will occur
        $this->paymentGateway->beforeFirstCharge(function ($paymentGateway) use ($concert) {
            $requestA = $this->app['request'];
            $res1 = $this->orderTickets($concert, [
                'email' => 'person_B@example.com',
                'ticket_quantity' => 2,
                'payment_token' => $this->paymentGateway->getValidTestToken(),
            ]);
            $this->app['request'] = $requestA;
            $res1->assertStatus(422);
            $this->assertEquals(0, $concert->orders()->where('orders.email', 'person_B@example.com')->count());
            $this->assertEquals(0, $this->paymentGateway->totalCharges());
        });

        $res = $this->orderTickets($concert, [
            'email' => 'person_A@example.com',
            'ticket_quantity' => 4,
            'payment_token' => $this->paymentGateway->getValidTestToken(),
        ]);

        $res->assertStatus(201);
        $this->assertEquals(48, $this->paymentGateway->totalCharges());
        // $this->assertEquals(4, $concert->orders()->where('email', 'person_A@example.com')->count());
    }


    /** @test */
    public function order_is_not_created_when_payment_fails()
    {
        $this->withoutExceptionHandling();
        $concert = factory(Concert::class)->state('published')->create([
            'ticket_price' => 3250
        ]);
        $concert->addTickets(4);

        $res = $this->orderTickets($concert, [
            'email' => 'john@example.com',
            'ticket_quantity' => 3,
            'payment_token' => 'invalid-token'
        ]);

        $res->assertStatus(422);

        // Make sure that an order exists for this customer
        $order = $concert->orders()->where('email', 'john@example.com')->first();
        $this->assertNull($order);
        $this->assertEquals(4, $concert->ticketsRemaining());
    }


    /** @test */
    public function email_is_req_for_purchase_tickets()
    {

        // $this->withoutExceptionHandling();

        $concert = factory(Concert::class)->state('published')->create(['ticket_price' => 3250]);
        $res = $this->orderTickets($concert, [
            'ticket_quantity' => 3,
            'payment_token' => $this->paymentGateway->getValidTestToken(),
        ]);
        $this->assertArrayHasKey('email', $res->decodeResponseJson()['errors']);
        $res->assertStatus(422);
    }


    /** @test */
    public function cannot_purchase_more_tickets_thank_remain()
    {
        $this->withoutExceptionHandling();
        $concert = factory(Concert::class)->state('published')->create();
        $concert->addTickets(50);

        $res = $this->orderTickets($concert, [
            'email' => 'john@example.com',
            'ticket_quantity' => 51,
            'payment_token' => $this->paymentGateway->getValidTestToken(),
        ]);

        $res->assertStatus(422);
        $order = $concert->orders()->where('email', 'john@example.com')->first();

        $this->assertNull($order);
        $this->assertEquals(0, $this->paymentGateway->totalCharges());
        $this->assertEquals(50, $concert->ticketsRemaining());
    }
}
