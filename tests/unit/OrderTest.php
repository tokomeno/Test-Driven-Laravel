<?php

namespace Tests\Unit;

use App\Concert;
use App\Order;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class OrderTest extends TestCase
{
	use DatabaseMigrations;
    /** @test */
    public function tickets_are_relase_when_an_order_is_cancled()
    {
        $concert = factory(Concert::class)->create();

        $concert->addTickets(10);

        $order = $concert->orderTickets('jane@example', 5);

        $this->assertEquals(5, $concert->ticketsRemaining());

        $order->cancel();

        $this->assertEquals(10, $concert->ticketsRemaining()); 
    	

    	$this->assertNull(Order::find($order->id));
    	$this->assertDatabaseMissing('orders', ['id' => $order->id]);

    }
}
