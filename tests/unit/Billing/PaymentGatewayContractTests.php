<?php

use App\Billing\PaymentFailException;

trait PaymentGatewayContractTests 
{
    
    abstract protected function getPaymentGateway();
    
     /** @test */
    public function charges_with_valid_payment_token_are_successful()
    {
        
        $paymentGateway = $this->getPaymentGateway(); 

        $newCharges = $paymentGateway->newChargesDuring(function($paymentGateway) {
            $paymentGateway->charge(2500, $paymentGateway->getValidTestToken());
        });

        $this->assertCount(1, $newCharges);
        $this->assertEquals(2500, $newCharges->sum());
    
    }

     /** @test */
    public function can_fetch_charges_created_during_a_callback()
    {
        $paymentGateway = $this->getPaymentGateway();
    
        $paymentGateway->charge(2000, $paymentGateway->getValidTestToken());
        $paymentGateway->charge(3000, $paymentGateway->getValidTestToken());

        $newCharges = $paymentGateway->newChargesDuring(function($paymentGateway){
             $paymentGateway->charge(4000, $paymentGateway->getValidTestToken());
            $paymentGateway->charge(5000, $paymentGateway->getValidTestToken());
        });

        $this->assertCount(2, $newCharges);
        $this->assertEquals([5000,4000], $newCharges->all());

    }

     /** @test */
    public function charges_with_an_ivalid_token_fail()
    {
    	// $this->withoutExceptionHandling();
        $paymentGateway = $this->getPaymentGateway();
        	
        $newCharges = $paymentGateway->newChargesDuring(function($paymentGateway) {
            try {
              $paymentGateway->charge(2500, 'invalid-token-bro');
            } catch (PaymentFailException $e) {
                return;
            }
            $this->fail('chargin with a invalid payemnt token did not throw payment faild exceptiiionion');
        }); 

        $this->assertCount(0, $newCharges);
    }
}