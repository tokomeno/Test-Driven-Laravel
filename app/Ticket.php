<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use App\Facades\TicketCode;

/**
 * App\Ticket
 *
 * @property int $id
 * @property int|null $order_id
 * @property int $concert_id
 * @property string|null $reserved_at
 * @property string|null $code
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Concert $concert
 * @property-read mixed $price
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Ticket available()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Ticket newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Ticket newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Ticket query()
 * @mixin \Eloquent
 */
class Ticket extends Model
{
    protected $guarded = ['id'];

    public function scopeAvailable($query)
    {
        return $query->whereNull('order_id')->whereNull('reserved_at');
    }

    public function scopeSold($query)
    {
        return $query->whereNotNull('order_id');
    }

    public function release()
    {
        $this->update([
            // 'order_id' => null,
            'reserved_at' => null
        ]);
    }

    public function concert()
    {
        return $this->belongsTo(Concert::class);
    }

    public function getPriceAttribute()
    {
        return $this->concert->ticket_price;
    }

    public function reserve()
    {
        $this->update(['reserved_at' => Carbon::now()]);
    }

    public function claimFor($order)
    {
        $this->code = TicketCode::generateFor($this);
        $order->tickets()->save($this);
    }
}
