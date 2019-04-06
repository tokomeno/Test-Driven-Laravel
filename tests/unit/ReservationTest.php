<?php

namespace Tests\Unit;

use App\Concert;
use App\Order;
use App\Reservation;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ReservationTest extends TestCase
{
	// use DatabaseMigrations;
    

    /** @test */
    public function calc_the_total_cost()
    {

   //  	$concert = factory(Concert::class)->create([
			// 'ticket_price' => 1200
   //  	])->addTickets(10);
   //  	$tickets = $concert->findTickets(3);

    	$tickets = collect([
			(object) ['price' => 1200],
			(object) ['price' => 1200],
			(object) ['price' => 1200],
    	]);

    	$res = new Reservation($tickets);


    	$this->assertEquals(3600, $res->totalCost());
    }
}