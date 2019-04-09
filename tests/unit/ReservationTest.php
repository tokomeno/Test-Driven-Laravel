<?php

namespace Tests\Unit;

use Mockery;
use App\Concert;
use App\Order;
use App\Reservation;
use App\Ticket;
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


    /** @test */
    public function reserved_tacks_are_releases_when_reservaion_is_calceled()
    {
        $ticket1 = Mockery::mock(Ticket::class);
        $ticket1->shouldReceive('release')->once();

        $ticket2 = Mockery::mock(Ticket::class);
        $ticket2->shouldReceive('release')->once();

        $ticket3 = Mockery::mock(Ticket::class);
        $ticket3->shouldReceive('release')->once();

        $tickets = collect([$ticket1, $ticket2, $ticket3]);

        $resevation = new Reservation($tickets);

        $resevation->cancel();
    }
}