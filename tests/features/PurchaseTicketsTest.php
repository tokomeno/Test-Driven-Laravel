<?php


use App\Billing\FakePaymentGateway;
use App\Billing\PaymentGateway;
use App\Concert;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Tests\TestCase;

class PurchaseTicketsTest extends TestCase
{
    use DatabaseMigrations;

    protected function setUp(){
        parent::setUp();

        $this->paymentGateway = new FakePaymentGateway();
        app()->instance(PaymentGateway::class, $this->paymentGateway);
    }

    private function orderTickets($concert, $params){
         return $this->json('POST', "/concerts/{$concert->id}/orders", $params);
    }

    /** @test */
    function customer_can_purchase_published_concert_tickets()
    {
        $this->withoutExceptionHandling();
        // Create a concert
        $concert = factory(Concert::class)->state('published')->create(['ticket_price' => 3250]);
        $concert->addTickets(3);

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
                'email' => 'john@example.com',
                'ticket_quantity' => 3,
                'amount' => 9750
            ]);
        // Make sure the customer was charged the correct amount
        $this->assertEquals(9750, $this->paymentGateway->totalCharges());

        // Make sure that an order exists for this customer
        $order = $concert->orders()->where('email', 'john@example.com')->first();
        $this->assertNotNull($order);
        $this->assertEquals(3, $order->tickets()->count());
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
        $this->paymentGateway->beforeFirstCharge(function($paymentGateway) use ($concert){
           $requestA = $this->app['request'];
            $res1 = $this->orderTickets($concert, [
                'email' => 'person_B@example.com',
                'ticket_quantity' => 2,
                'payment_token' => $this->paymentGateway->getValidTestToken(),
            ]);
            $this->app['request'] = $requestA ;
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
        $this->assertEquals(4, $concert->orders()->where('email', 'person_A@example.com')->count()); 
    }


    /** @test */
    public function order_is_not_created_when_payment_fails()
    {   
        $this->withoutExceptionHandling();
        $concert = factory(Concert::class)->state('published')->create([
            'ticket_price' => 3250
        ]);
        $concert->addTickets(4);

        $res = $this->orderTickets($concert,  [
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
