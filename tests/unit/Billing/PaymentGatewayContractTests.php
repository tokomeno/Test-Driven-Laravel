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
        $this->assertEquals(2500, $newCharges->map->amount()->sum());
    
    }

    /** @test */
    public function can_get_details_about_successf_charge()
    {
        $paymentGateway = $this->getPaymentGateway(); 

        $charge = $paymentGateway->charge(2500, $paymentGateway->getValidTestToken($paymentGateway::TEST_CARD_TOKEN));
        
        $this->assertEquals(substr($paymentGateway::TEST_CARD_TOKEN, -4), $charge->cardLastFour());
        $this->assertEquals(2500, $charge->amount());
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
        $this->assertEquals([5000,4000], $newCharges->map->amount()->all());

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