<?php

namespace App\Billing;

use App\Billing\Charge;
use App\Billing\PaymentGateway;

class FakePaymentGateway implements PaymentGateway
{
    private $charges;
    private $tokens;
    private $beforeFirstChargeCallback;
    const TEST_CARD_TOKEN = '4242424242424242';

    public function __construct()
    {
        $this->charges = collect([]);
        $this->tokens = collect([]);
    }

     
    protected function getPaymentGateway()
    {
        return new StripePaymentGateway(config('services.stripe.secret'));
    }


    public function getValidTestToken($cardNumber = self::TEST_CARD_TOKEN)
    {
        $token = 'fake-tok_'.str_random(24);
        $this->tokens[$token] = $cardNumber;
        return $token;
    }

    public function totalCharges()
    {
        // return $this->charges->sum();
        return $this->charges->map->amount()->sum();
    }

    public function charge($amount, $token)
    {
        // dump($this->beforeFirstChargeCallback);
        if ($this->beforeFirstChargeCallback !== null) {
            $callback = $this->beforeFirstChargeCallback;
            $this->beforeFirstChargeCallback = null;
            $callback->__invoke($this);
        }
        if (! $this->tokens->has($token)) {
            throw new PaymentFailException;
        }
        return $this->charges[] = new Charge([
            'amount' => $amount,
            'card_last_four' => substr($this->tokens[$token], -4),
        ]);
    }

    public function beforeFirstCharge($callback)
    {
        $this->beforeFirstChargeCallback = $callback;
    }


    public function newChargesDuring($callback)
    {
        $chargesFrom = $this->charges->count();
        $callback($this);

        return $this->charges->slice($chargesFrom)->reverse()->values();
    }
}
