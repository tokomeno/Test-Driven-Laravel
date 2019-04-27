<?php

namespace App;

use App\Order;

class Reservation 
{
	private $tickets;
	private $email;

	public function __construct($tickets, $email)
	{
		$this->tickets = $tickets;
		$this->email = $email;
	}
	

	public function totalCost(){
		return $this->tickets->sum('price');
	}

	public function tickets(){
		return $this->tickets;
	}

	public function email(){
		return $this->email;
	}

	public function complete($paymentGateway, $paymentToken){
	
		$charge = $paymentGateway->charge($this->totalCost(), $paymentToken);
		// dump($charge);
		$order = Order::forTickets($this->tickets(), $this->email,   $charge);
		return $order;
	}

	public function cancel(){
		foreach ($this->tickets as $ticket) {
			$ticket->release();
		}
	}
	
}