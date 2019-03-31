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

    /** @test */
    function customer_can_purchase_concert_tickets()
    {
        
       
       
        
        // Create a concert
        $concert = factory(Concert::class)->create(['ticket_price' => 3250]);

        // Act
        // Purchase concert tickets
        $res = $this->json('POST', "/concerts/{$concert->id}/orders", [
            'email' => 'john@example.com',
            'ticket_quantity' => 3,
            'payment_token' => $this->paymentGateway->getValidTestToken(),
        ]);

        $res->assertStatus(201);
        // Assert
        // Make sure the customer was charged the correct amount
        $this->assertEquals(9750, $this->paymentGateway->totalCharges());

        // Make sure that an order exists for this customer
        $order = $concert->orders()->where('email', 'john@example.com')->first();
        $this->assertNotNull($order);
        $this->assertEquals(3, $order->tickets()->count());
    }


    /** @test */
    public function email_is_req_for_purchase_tockets()
    {       
         
        // $this->withoutExceptionHandling();
      
        $concert = factory(Concert::class)->create(['ticket_price' => 3250]);
        $res = $this->json('POST', "/concerts/{$concert->id}/orders", [
            'ticket_quantity' => 3,
            'payment_token' => $this->paymentGateway->getValidTestToken(),
        ]);
        $this->assertArrayHasKey('email', $res->decodeResponseJson()['errors']);
        $res->assertStatus(422);
    }
}
