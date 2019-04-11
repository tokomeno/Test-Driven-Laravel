<?php

namespace App\Billing;

use Stripe\Charge;


class StripePaymentGateway implements PaymentGateway
{
	private $apiKey;

	public function __construct($apiKey)
	{
		$this->apiKey = config('services.stripe.key');
	}

    public function charge($amount, $token)
    {
		Charge::create([
			'amount' => $amount,
			'source' => $token,
			'currency' => 'usd'
		],['api_key' => $this->apiKey]);
    }
}
