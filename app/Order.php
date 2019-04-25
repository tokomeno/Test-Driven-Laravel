<?php

namespace App;

use App\Concert;
use App\Facades\OrderConfirmationNumber;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $guarded = [];
    
    public static function findByConfirmationNumber($orderConfirmation){
        return self::where('confirmation_number', $orderConfirmation)->firstOrFail();
    }

    public static function forTickets($tickets, $email, $amount)
    {
        $order = self::create([ 
            // 'confirmation_number' => app(OrderConfirmationNumberGenerator::class)->generate(),
            'confirmation_number' => OrderConfirmationNumber::generate(),
            'email' => $email,
            'amount' => $amount
        ]);

        foreach($tickets  as $ticket){
            $order->tickets()->save($ticket);
        }

        return $order;    
    }



    public function tickets()
    {
    	return $this->hasMany(Ticket::class);
    }

    public function ticketQuantity(){
    	return $this->tickets()->count();
    }

    public function concert()
    {
    	return $this->belongsTo(Concert::class);
    }
	
    public function toArray(){
        return [
            'confirmation_number' => $this->confirmation_number,
            'email' => $this->email,
            'ticket_quantity' => $this->ticketQuantity(),
            'amount' => $this->amount
        ];
    }

    
        
    // not using anymore

    // public static function fromReservation($reservation){
    //      $order = self::create([ 
    //         'email' => $reservation->email(),
    //         'amount' => $reservation->totalCost()
    //     ]);

    //     $order->tickets()->saveMany($reservation->tickets());
    
    //     return $order;   
    // }

    // public function cancel(){
    //  foreach ($this->tickets as $ticket) {
    //      $ticket->release();
    //  }
    //  // $this->tickets()->update(['order_id' => null]);

    //  $this->delete();
    // }

}
