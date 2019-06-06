<?php

namespace App\Billing;

use App\Billing\PaymentFailException;
use Stripe\Error\InvalidRequest;

class StripePaymentGateway implements PaymentGateway
{
    private $apiKey;
    const TEST_CARD_TOKEN = '4242424242424242';

    public function __construct($apiKey)
    {
        $this->apiKey = config('services.stripe.key');
    }

    public function charge($amount, $token)
    {
        try {
            $stripeCharge = \Stripe\Charge::create([
                'amount' => $amount,
                'source' => $token,
                'currency' => 'usd'
            ], ['api_key' => $this->apiKey]);

            return new Charge([
                'amount' => $stripeCharge['amount'],
                'card_last_four' => $stripeCharge['source']['last4']
            ]);
        } catch (InvalidRequest $e) {
            throw new PaymentFailException();
        }
    }

    public function getValidTestToken($cardNumber = self::TEST_CARD_TOKEN)
    {
        return \Stripe\Token::create([
          'card' => [
            'number' => $cardNumber,
            'exp_month' => 4,
            'exp_year' => date('Y') + 1,
            'cvc' => '314'
          ]
        ], ['api_key' => $this->apiKey])->id;
    }


    public function newChargesDuring($callback)
    {
        $latestCharge = $this->lastCharge();
    
        $callback($this);

        return  $this->newChargesSince($latestCharge)->map(function ($lastCharge) {
            return new Charge([
                'amount' => $lastCharge['amount'],
                'card_last_four' => $lastCharge['source']['last4']
            ]);
        });
    }


    private function lastCharge()
    {
        return \Stripe\Charge::all(
            ['limit' => 1],
            ['api_key' => $this->apiKey]
        )['data'][0];
    }

    private function newChargesSince($charge = null)
    {
        $newCharges = \Stripe\Charge::all(
            [
                'ending_before' => $charge ? $charge->id :null
            ],
            ['api_key' => $this->apiKey ]
        )['data'];

        return collect($newCharges);
    }
}
