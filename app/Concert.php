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
        // return $this->hasMany(Order::class);
         return $this->belongsToMany(Order::class, 'tickets');
    }

    public function tickets()
    {
        return $this->hasMany(Ticket::class);
    }

    public function orderTickets($email, $t_q){
        
        $tickets = $this->findTickets($t_q);

        $order = $this->createOrder($email, $tickets);

        
        return $order;
    }

    public function addTickets($q){
        foreach (range(1, $q) as $i) {
            $this->tickets()->create([]);
        }
        return $this;
    }

    public function ticketsRemaining(){
        return $this->tickets()
                ->available()
                ->count();
    }

    public function findTickets($quantity)
    {
        $tickets = $this->tickets()->available()
            ->take($quantity)
            ->get();
        if($tickets->count() < $quantity){
            throw new NotEnoughTicketsException;
        }
        return $tickets;
    }

    public function reserveTickets($q){
        return $this->findTickets($q)->each(function($ticket){
            $ticket->reserve();
        });
    }

    public function createOrder($email, $tickets){
       return Order::forTickets($tickets, $email, $tickets->sum('price'));
    }
}
