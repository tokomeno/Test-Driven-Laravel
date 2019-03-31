<?php

namespace App\Http\Controllers;

use App\Billing\PaymentFailException;
use App\Billing\PaymentGateway;
use App\Concert;
use Illuminate\Http\Request;

class ConcertOrdersController extends Controller
{
	private $paymentGateway;

	public function __construct(PaymentGateway $paymentGateway)
	{
		$this->paymentGateway = $paymentGateway;
	}

    public function store($concertId){
	
		$this->validate(request(), [
			'email' => 'required|email'
		]);
	try{


		$concert = Concert::find($concertId);
		$ticketQuantity = request('ticket_quantity');
		$amount = $ticketQuantity * $concert->ticket_price;
		$token = request('payment_token');
    	$this->paymentGateway->charge($amount, $token);
    	
		$order = $concert->orderTickets(request('email'), $ticketQuantity);
    	return response()->json([], 201);

        } catch (PaymentFailException $e) {
               	return response()->json([], 422);

        }
    }
}
