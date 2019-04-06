<?php

namespace App\Billing;

use App\Billing\PaymentGateway;

class FakePaymentGateway implements PaymentGateway {
	private $charges;
	private $beforeFirstChargeCallback;

	public function __construct()
	{
		$this->charges = collect([]);
	}


	public function getValidTestToken(){
		return 'valid-token';
	}

	public function totalCharges(){
		return $this->charges->sum();
	}

	public function charge($amount, $token){
		// dump($this->beforeFirstChargeCallback);
		if($this->beforeFirstChargeCallback !== null){
			$this->beforeFirstChargeCallback->__invoke($this);
		}
		if($token !== $this->getValidTestToken()){
			throw new PaymentFailException;
		}
		$this->charges->push($amount);
	}

	public function beforeFirstCharge($callback){
		$this->beforeFirstChargeCallback = $callback;
	}
}