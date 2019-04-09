<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
	protected $guarded = ['id'];
    public function scopeAvailable($query){
    	return $query->whereNull('order_id')->whereNull('reserved_at');
    }

    public function release(){
    	$this->update([
            // 'order_id' => null, 
            'reserved_at' => null
        ]);
    }

    public function concert()
    {
    	return $this->belongsTo(Concert::class);
    }

    public function getPriceAttribute(){
    	return $this->concert->ticket_price;
    }

    public function reserve(){
        $this->update(['reserved_at' => Carbon::now() ]);
    }
}
