<?php


use App\Billing\FakePaymentGateway;
use App\Concert;
use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithoutMiddleware;

class PurchaseTicketsTest extends TestCase
{
    use DatabaseMigrations;
    /** @test */
    function customer_can_purchase_concert_tickets()
    {
        
        // Arrange
        $paymentGateway = new FakePaymentGateway();
        
        // Create a concert
        $concert = factory(Concert::class)->create(['ticket_price' => 3250]);

        // Act
        // Purchase concert tickets
        $this->json('POST', "/concerts/{$concert->id}/orders", [
            'email' => 'john@example.com',
            'ticket_quantity' => 3,
            'payment_token' => $paymentGateway->getValidTestToken(),
        ]);

        // Assert
        // Make sure the customer was charged the correct amount
        $this->assertEquals(9750, $paymentGateway->totalCharges());

        // Make sure that an order exists for this customer
        $order = $concert->orders()->where('email', 'john@example.com')->first();
        $this->assertNotNull($order);
        $this->assertEquals(3, $order->tickets->count());
    }
}
