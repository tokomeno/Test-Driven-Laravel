<?php

namespace Tests\Unit;

use App\Concert;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class TicketTest extends TestCase
{
	use DatabaseMigrations;
    /** @test */
    public function a_ticket_can_be_release()
    {
    	$concert = factory(Concert::class)->state('published')->create();
    	$concert->addTickets(1);
    	$order = $concert->orderTickets('j@mail.com', 1);
    	$ticket = $order->tickets()->first();

    	$this->assertEquals($order->id, $ticket->order_id);

    	$ticket->release();
		
		$this->assertNull($ticket->fresh()->order_id);


    }
}
