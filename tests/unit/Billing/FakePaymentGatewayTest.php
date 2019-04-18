
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
    
    use PaymentGatewayContractTests;
  
    protected function getPaymentGateway()
    {
        return new FakePaymentGateway;
    }



    /** @test */
    public function running_a_hook_before_the_first_charge()
    {
        $paymentGateway = new FakePaymentGateway;
        $timeCallbackRan = 0;

        $paymentGateway->beforeFirstCharge(function($paymentGateway) use (&$timeCallbackRan){
                $timeCallbackRan++;
                $paymentGateway->charge(2500, $paymentGateway->getValidTestToken());
                $this->assertEquals(2500,  $paymentGateway->totalCharges());
        });

        $paymentGateway->charge(2500, $paymentGateway->getValidTestToken()); 
        $this->assertEquals(5000,  $paymentGateway->totalCharges());
        $this->assertEquals(1, $timeCallbackRan);
        
    }

}
