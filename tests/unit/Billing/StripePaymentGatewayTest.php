<?php

namespace Tests\Unit\Billing;

use App\Billing\StripePaymentGateway;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class StripePaymentGatewayTest extends TestCase
{
	 use DatabaseMigrations;
    


    /** @test */
    public function charges_with_a_valid_payment_token_are_succesfuel()
    {
    

		$paymentGateway = new StripePaymentGateway(['api_key' => config('services.stripe.key')]); 

		$token = \Stripe\Token::create([
		  'card' => [
		    'number' => '4242424242424242',
		    'exp_month' => 4,
		    'exp_year' => date('Y') + 1,
		    'cvc' => '314'
		  ]
		], ['api_key' => config('services.stripe.key')])->id;

		 

		$paymentGateway->charge(2500, $token);

        // Create a new charge for some amount using a valid token
  
		$lastCharge = \Stripe\Charge::all(
			['limit' => 1], 
			['api_key' => config('services.stripe.key')]
		)['data'][0];
		// dd($lastCharge);


		$this->assertEquals(2500, $lastCharge->amount);
    }	
   
}
