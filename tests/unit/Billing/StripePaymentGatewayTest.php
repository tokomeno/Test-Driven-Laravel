<?php

namespace Tests\Unit\Billing;

use App\Billing\PaymentFailException;
use App\Billing\StripePaymentGateway;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

/**
*	@group integration 
*/
class StripePaymentGatewayTest extends TestCase
{
	 use DatabaseMigrations;
	 use \PaymentGatewayContractTests;

	 protected function setUp(){
	 	parent::setUp();
	 	$this->lastCharge = $this->lastCharge();
	 }
    
	private function lastCharge(){
		return \Stripe\Charge::all(
			['limit' => 1], 
			['api_key' => config('services.stripe.key')]
		)['data'][0];
	}

	private function newCharges(){
		return \Stripe\Charge::all(
			[
				'limit' => 1,
				'ending_before' => $this->lastCharge->id
			], 
			['api_key' => config('services.stripe.key')]
		)['data'];
	}

 
	protected function getPaymentGateway()
	{
	    return new StripePaymentGateway(config('services.stripe.secret'));
	}


     /** @test */
    public function charges_with_an_ivalid_token_fail()
    {
    	$this->withoutExceptionHandling();
    	  
        try {
        	$paymentGateway = new StripePaymentGateway(config('services.stripe.key')); 

        	$paymentGateway->charge(200, 'invalid-token');
       
        } catch (PaymentFailException $e) {
        	$this->assertTrue(true);
        	$this->assertCount(0, $this->newCharges());
        	return;
        }

        $this->fail('chargin with a invalid payemnt token did not throw payment faild exceptiiionion');

    }
   
}
