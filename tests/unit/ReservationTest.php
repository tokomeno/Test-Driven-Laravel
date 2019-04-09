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
	use DatabaseMigrations;
    

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

    	$res = new Reservation($tickets, 'tok@mail.com');


    	$this->assertEquals(3600, $res->totalCost());
    }

     /** @test */
    public function retraivng_the_reserved_tickets()
    {

   //   $concert = factory(Concert::class)->create([
      // 'ticket_price' => 1200
   //   ])->addTickets(10);
   //   $tickets = $concert->findTickets(3);

      $tickets = collect([
      (object) ['price' => 1200],
      (object) ['price' => 1200],
      (object) ['price' => 1200],
      ]);

      $res = new Reservation($tickets, 'tok@mail.com');


      $this->assertEquals($tickets, $res->tickets());
    }

      /** @test */
    public function retraivng_the_reserved__customer_Email()
    {
 
      $res = new Reservation([], 'tok@mail.com');
 
      $this->assertEquals('tok@mail.com', $res->email());

    }



    /** @test */
    public function reserved_tacks_are_releases_when_reservaion_is_calceled()
    {

      // VERSION ONE
        // $ticket1 = Mockery::mock(Ticket::class);
        // $ticket1->shouldReceive('release')->once();

        // $ticket2 = Mockery::mock(Ticket::class);
        // $ticket2->shouldReceive('release')->once();

        // $ticket3 = Mockery::mock(Ticket::class);
        // $ticket3->shouldReceive('release')->once();
        // $tickets = collect([$ticket1, $ticket2, $ticket3]);
     
      // VERSION TWO
        $tickets = collect([
          Mockery::mock(Ticket::class, function($mock){
            $mock->shouldReceive('release')->once();
          }), 
          Mockery::mock(Ticket::class)->shouldReceive('release')->once()->getMock(),
          Mockery::mock(Ticket::class)->shouldReceive('release')->once()->getMock()
        ]);

         $resevation = new Reservation($tickets, 'tok@mail.com');

        $resevation->cancel();




//not works
      // VERSION THEEE
        //  $tickets = collect([
        //   Mockery::spy(Ticket::class),
        //   Mockery::spy(Ticket::class),
        //   Mockery::spy(Ticket::class)
        // ]);

        // $resevation = new Reservation($tickets);

        // $resevation->cancel();

        // $tickets->map(function($ticket){
        //   $ticket->shouldReceived('release');
        // }); 
    }

    /** @test */
    public function complieing_a_reseravtion()
    {
        $concert = factory(Concert::class)->create([ 'ticket_price' => 1200])->addTickets(3);
        $tickets =  $concert->tickets;
        $reservation = new Reservation($tickets, 'tok@mail.com');    
       
        $order = $reservation->complete();

        $this->assertEquals('tok@mail.com', $order->email);
        $this->assertEquals(3, $order->ticketQuantity());
        $this->assertEquals(3600, $order->amount);
    }


}