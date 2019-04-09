<?php

namespace Tests\Unit;

use App\Concert;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class TicketTest extends TestCase
{
	use DatabaseMigrations;
    
    /** @test */
    public function a_ticket_can_be_reserved()
    {
        
        $ticket = factory('App\Ticket')->create();
        $this->assertNull($ticket->fresh()->reserved_at);

        $ticket->reserve();
        
        $this->assertNotNull($ticket->fresh()->reserved_at);


    }

    /** @test */
    public function a_ticket_can_be_release()
    {
  //   	$concert = factory(Concert::class)->state('published')->create();
  //   	$concert->addTickets(1);
  //   	$order = $concert->orderTickets('j@mail.com', 1);
  //   	$ticket = $order->tickets()->first();

  //   	$this->assertEquals($order->id, $ticket->order_id);

  //   	$ticket->release();
		
		// $this->assertNull($ticket->fresh()->order_id);


        $ticket = factory('App\Ticket')->states('reserved')->create(['reserved_at' => Carbon::now()]);
        $this->assertNotNull($ticket->reserved_at);

        $ticket->release();

        $this->assertNull($ticket->reserved_at);

    }
}
