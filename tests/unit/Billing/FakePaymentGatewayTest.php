
<?php

use App\Billing\FakePaymentGateway;
use App\Billing\PaymentFailException;
use App\Concert;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Tests\TestCase;

class FakePaymentGatewayTest extends TestCase
{
    use DatabaseMigrations;

    /** @test */
    public function charges_with_valid_payment_token_are_successful()
    {
        $paymentGateway = new FakePaymentGateway;

        $paymentGateway->charge(2500, $paymentGateway->getValidTestToken());

        $this->assertEquals(2500, $paymentGateway->totalCharges());
    }

    /** @test */
    public function charges_with_an_ivalid_token_fail()
    {
    	// $this->withoutExceptionHandling();
      
        try {
        	$paymentGateway = new FakePaymentGateway;
        	$paymentGateway->charge(200, 'invalid-token');
       
        } catch (PaymentFailException $e) {
        	$this->assertTrue(true);
        	return;
        }

        $this->fail();


    }

}
