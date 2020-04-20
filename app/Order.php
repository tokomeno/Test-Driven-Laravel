<?php

namespace App;

use App\Concert;
use App\Facades\OrderConfirmationNumber;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Order
 *
 * @property int $id
 * @property string $email
 * @property int $amount
 * @property string $confirmation_number
 * @property string $card_last_four
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Concert $concert
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Ticket[] $tickets
 * @property-read int|null $tickets_count
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Order newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Order newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Order query()
 * @mixin \Eloquent
 */
class Order extends Model
{
    protected $guarded = [];

    public static function findByConfirmationNumber($orderConfirmation)
    {
        return self::where('confirmation_number', $orderConfirmation)->firstOrFail();
    }

    public static function forTickets($tickets, $email,  $charge)
    {
        // dd($charge);
        $order = self::create([
            'confirmation_number' => OrderConfirmationNumber::generate(),
            'email' => $email,
            'amount' => $charge->amount(),
            'card_last_four' => $charge->cardLastFour(),
        ]);

        // $order->tickets()->saveMany($tickets);
        // foreach($tickets  as $ticket){
        //     $order->tickets()->save($ticket);
        // }
        $tickets->each->claimFor($order);

        return $order;
    }



    public function tickets()
    {
        return $this->hasMany(Ticket::class);
    }

    public function ticketQuantity()
    {
        return $this->tickets()->count();
    }

    public function concert()
    {
        return $this->belongsTo(Concert::class);
    }

    public function toArray()
    {
        return [
            'confirmation_number' => $this->confirmation_number,
            'email' => $this->email,
            'amount' => $this->amount,
            'ticket_quantity' => $this->ticketQuantity(),
            'tickets' => $this->tickets->map(function ($ticket) {
                return ['code' => $ticket->code];
            })->all()
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
