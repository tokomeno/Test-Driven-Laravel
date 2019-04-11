<?php

namespace App\Billing;

use App\Billing\PaymentFailException;
use Stripe\Charge;
use Stripe\Error\InvalidRequest;


class StripePaymentGateway implements PaymentGateway
{
	private $apiKey;

	public function __construct($apiKey)
	{
		$this->apiKey = config('services.stripe.key');
	}

    public function charge($amount, $token)
    {
		try{
			Charge::create([
				'amount' => $amount,
				'source' => $token,
				'currency' => 'usd'
			],['api_key' => $this->apiKey]);
		}catch(InvalidRequest $e){
			throw new PaymentFailException();
		}
    }
}
