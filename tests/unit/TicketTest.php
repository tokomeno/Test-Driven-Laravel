<?php

namespace Tests\Unit;

use App\Order;
use App\Ticket;
use App\Concert;
use Carbon\Carbon;
use Tests\TestCase;
use App\Facades\TicketCode;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\DatabaseMigrations;

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


    /** @test */
    public function a_tickket_can_be_claimed_for_order()
    {
        $order = factory(Order::class)->create();
        $ticket = factory(Ticket::class)->create();

        TicketCode::shouldReceive('generateFor')->with($ticket)->andReturn('TICKETCDE1');

        $this->assertNull($ticket->code);

        $ticket->claimFor($order);

        // $this->assertEquals($order->id, $ticket->order_id);
        $this->assertContains($order->id, $order->tickets->pluck('id'));
        $this->assertEquals('TICKETCDE1', $ticket->code);
    }
}
