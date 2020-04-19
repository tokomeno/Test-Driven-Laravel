<?php

namespace App;

use App\Exceptions\NotEnoughTicketsException;
use App\Reservation;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Concert
 *
 * @property int $id
 * @property string $title
 * @property string $subtitle
 * @property \Illuminate\Support\Carbon $date
 * @property int $ticket_price
 * @property string $venue
 * @property string $venue_address
 * @property string $city
 * @property string $state
 * @property string $zip
 * @property string $additional_information
 * @property string|null $published_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read mixed $formatted_date
 * @property-read mixed $formatted_start_time
 * @property-read mixed $ticket_price_in_dollars
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Order[] $orders
 * @property-read int|null $orders_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Ticket[] $tickets
 * @property-read int|null $tickets_count
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Concert newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Concert newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Concert published()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Concert query()
 * @mixin \Eloquent
 */
class Concert extends Model
{
    protected $guarded = [];
    protected $dates = ['date'];


    public function user()
    {
        return $this->belongsTo('App\User');
    }

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

    public function getTicketQuantityAttribute()
    {
        return $this->tickets()->count();
    }

    public function orders()
    {
        // return $this->hasMany(Order::class);
        return $this->belongsToMany(Order::class, 'tickets');
    }

    public function tickets()
    {
        return $this->hasMany(Ticket::class);
    }


    public function addTickets($q): Concert
    {
        foreach (range(1, $q) as $i) {
            $this->tickets()->create([]);
        }
        return $this;
    }

    public function ticketsRemaining()
    {
        return $this->tickets()
            ->available()
            ->count();
    }

    public function findTickets($quantity)
    {
        $tickets = $this->tickets()->available()
            ->take($quantity)
            ->get();
        if ($tickets->count() < $quantity) {
            throw new NotEnoughTicketsException;
        }
        return $tickets;
    }

    public function reserveTickets($q, $email)
    {
        $tickets = $this->findTickets($q)->each(function ($ticket) {
            $ticket->reserve();
        });
        return new Reservation($tickets, $email);
    }
    public function unReserveTickets($q)
    {
        return $this->tickets()->take($q)->get()->each(function ($ticket) {
            $ticket->update(['reserved_at' => null]);
        });
    }

    public function ordersFor($customerEmail)
    {
        return $this->orders()->where('email', $customerEmail)->get();
    }



    //  public function orderTickets($email, $t_q){

    //     $tickets = $this->findTickets($t_q);
    //     $order = $this->createOrder($email, $tickets);
    //     return $order;
    // }

    // public function createOrder($email, $tickets){
    //    return Order::forTickets($tickets, $email, $tickets->sum('price'));
    // }
}
