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

	private function validToken(){
		return \Stripe\Token::create([
		  'card' => [
		    'number' => '4242424242424242',
		    'exp_month' => 4,
		    'exp_year' => date('Y') + 1,
		    'cvc' => '314'
		  ]
		], ['api_key' => config('services.stripe.key')])->id;
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

    /** @test */
    public function charges_with_a_valid_payment_token_are_succesfuel()
    {
  
		$paymentGateway = new StripePaymentGateway(['api_key' => config('services.stripe.key')]); 
		

		$paymentGateway->charge(4500, $this->validToken());

        // Create a new charge for some amount using a valid token
		$this->assertCount(1, $this->newCharges());
		$this->assertEquals(4500, $this->lastCharge()->amount);
    }	


     /** @test */
    public function charges_with_an_ivalid_token_fail()
    {
    	$this->withoutExceptionHandling();
    	  
        try {
        	$paymentGateway = new StripePaymentGateway(['api_key' => config('services.stripe.key')]); 

        	$paymentGateway->charge(200, 'invalid-token');
       
        } catch (PaymentFailException $e) {
        	$this->assertTrue(true);
        	$this->assertCount(0, $this->newCharges());
        	return;
        }

        $this->fail('chargin with a invalid payemnt token did not throw payment faild exceptiiionion');

    }
   
}
