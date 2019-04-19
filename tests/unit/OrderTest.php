<?php

namespace Tests\Unit;

use App\Concert;
use App\Order;
use App\Reservation;
use App\Ticket;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class OrderTest extends TestCase
{
	use DatabaseMigrations;
    
    /** @test */
    public function it_can_find_order_by_conffirmaiton_num()
    {
        $order = factory('App\Order')->create([
            'confirmation_number' => 4141242141
        ]);
        $foundOrder = Order::findByConfirmationNumber($order->confirmation_number);
        $this->assertEquals($order->id, $foundOrder->id);
    }

    /** @test */
    public function non_existence_order_by_conf_throws_an_exception()
    {
        
        try {
            Order::findByConfirmationNumber(232321321231);
        } catch (ModelNotFoundException $e) {
            $this->assertTrue(true);
           return;
        }
        $this->fail('should thow exception when not found lol');
         
    }

    /** @test */
    public function create_an_order_from_tickets_and_email()
    {
        $concert = factory(Concert::class)->create([ 'ticket_price' => 1200])->addTickets(5);
        $this->assertEquals(5, $concert->ticketsRemaining());
      // dd($concert->findTickets(3));
        $order = Order::forTickets($concert->findTickets(3), 'tok@mail.com', 3600);
        // dd($order->toArray());/
        $this->assertEquals('tok@mail.com', $order->email);
        $this->assertEquals(3, $order->ticketQuantity());
        $this->assertEquals(3600, $order->amount);
        $this->assertEquals(2, $concert->ticketsRemaining());


    }

    /** @test */
    // public function creatong_a_order_from_reservation()
    // {   
    //     $concert = factory(Concert::class)->create([ 'ticket_price' => 1200])->addTickets(3);
    //     $tickets =  $concert->tickets;
    //     $reservation = new Reservation($tickets, 'tok@mail.com');


    //     $order = Order::fromReservation($reservation, 'tok@mail.com');

    //     $this->assertEquals('tok@mail.com', $order->email);
    //     $this->assertEquals(3, $order->ticketQuantity());
    //     $this->assertEquals(3600, $order->amount);
    // }

    /** @test */
    public function converting_to_an_array()
    {
        $concert = factory(Concert::class)->create([ 'ticket_price' => 1200])->addTickets(5);
        $order = $concert->orderTickets('jane@example', 5);

        $res = $order->toArray();

        $this->assertEquals([
            'email' => 'jane@example',
            'ticket_quantity' => 5,
            'amount' => 6000
        ], $res); 
    }


    /** @test */
    // public function tickets_are_relase_when_an_order_is_cancled()
    // {
    //     $concert = factory(Concert::class)->create();

    //     $concert->addTickets(10);

    //     $order = $concert->orderTickets('jane@example', 5);

    //     $this->assertEquals(5, $concert->ticketsRemaining());

    //     $order->cancel();

    //     $this->assertEquals(10, $concert->ticketsRemaining()); 
    	

    // 	$this->assertNull(Order::find($order->id));
    // 	$this->assertDatabaseMissing('orders', ['id' => $order->id]);

    // }
}
