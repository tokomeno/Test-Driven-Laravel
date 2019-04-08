<?php

namespace App\Http\Controllers;

use App\Billing\PaymentFailException;
use App\Billing\PaymentGateway;
use App\Concert;
use App\Exceptions\NotEnoughTicketsException;
use App\Order;
use App\Reservation;
use Illuminate\Http\Request;

class ConcertOrdersController extends Controller
{
	private $paymentGateway;

	public function __construct(PaymentGateway $paymentGateway)
	{
		$this->paymentGateway = $paymentGateway;
	}

    public function store($concertId){
	
		$this->validate(request(), ['email' => 'required|email']);
		
		$concert = Concert::published()->findOrFail($concertId);

		try{
			
			$tickets = $concert->reserveTickets(request('ticket_quantity'));
			$reservation = new Reservation($tickets);
		 
			$this->paymentGateway->charge($reservation->totalCost(), request('payment_token') ); 

			$order = Order::forTickets($tickets, request('email'), $reservation->totalCost());

	    	return response()->json($order->toArray(), 201);

        } catch (PaymentFailException $e) {
        		// $order->cancel();
               	return response()->json([], 422);
        } catch (NotEnoughTicketsException $e) {
               	return response()->json([], 422);
        }
    }
}
