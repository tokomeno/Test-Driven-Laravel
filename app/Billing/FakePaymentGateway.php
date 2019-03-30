<?php

namespace App\Billing;

class FakePaymentGateway {

	public function getValidTestToken(){
		return 'valid-token';
	}

	public function TotalCharges(){
		return 9750;
	}
}