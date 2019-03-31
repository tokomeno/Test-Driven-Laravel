<?php

namespace App;

use App\Exceptions\NotEnoughTicketsException;
use Illuminate\Database\Eloquent\Model;

class Concert extends Model
{
    protected $guarded = [];
    protected $dates = ['date'];

    public function scopePublished($query)
    {
        return $query->whereNotNull('published_at');
    }

    public function getFormattedDateAttribute()
    {
        return $this->date->format('F j, Y');
    }

    public function getFormattedStartTimeAttribute()
    {
        return $this->date->format('g:ia');
    }

    public function getTicketPriceInDollarsAttribute()
    {
        return number_format($this->ticket_price / 100, 2);
    }
    public function orders(){
        return $this->hasMany(Order::class);
    }

    public function tickets()
    {
        return $this->hasMany(Ticket::class);
    }

    public function orderTickets($email, $t_q){
        
        $tickets = $this->tickets()->available()->take($t_q)->get();

        if($tickets->count() < $t_q){
            throw new NotEnoughTicketsException;
        }

        $order = $this->orders()->create(['email' => $email]);

        foreach($tickets  as $ticket){
            $order->tickets()->save($ticket);
        }
        return $order;
    }

    public function addTickets($q){
        foreach (range(1, $q) as $i) {
            $this->tickets()->create([]);
        }
    }

    public function ticketsRemaining(){
        return $this->tickets()
                ->available()
                ->count();
    }
}
