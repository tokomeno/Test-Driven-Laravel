<?php

namespace App\Billing;

use App\Billing\PaymentGateway;

class FakePaymentGateway implements PaymentGateway {
	private $charges;

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
		if($token !== $this->getValidTestToken()){
			throw new PaymentFailException;
		}
		$this->charges->push($amount);
	}
}