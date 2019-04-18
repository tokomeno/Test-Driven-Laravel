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
	 // use DatabaseMigrations;
	 use \PaymentGatewayContractTests;

	protected function getPaymentGateway()
	{
	    return new StripePaymentGateway(config('services.stripe.secret'));
	}
}
