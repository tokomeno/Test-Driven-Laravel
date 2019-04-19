<?php

namespace Tests\Feature;

use App\Concert;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ViewOrderTest extends TestCase
{
    use DatabaseMigrations;

   /** @test */
   public function user_can_view_their_order_confirmation()
   {
       // Crreate concert
   		$concert = factory(Concert::class)->create();
       // Crreate an order

   		$order = factory('App\Order')->create([
   			'confirmation_number' => 'tokodskodaks'
   		]);
       
       // Crreate some tickets

   		$ticket = factory('App\Ticket')->create([
			'order_id'=> $order->id,
   			'concert_id'=> $concert->id,
   		]);

   		// Visit the order confirmation page
		$response = $this->get("/orders/{$order->confirmation_number}");
	
		$response->assertStatus(200);
		$response->assertViewHas('order', function($viewOrder) use($order){
			return $order->id == $viewOrder->id;
		});
   		// Assert we see the concert orde deatauls
   }
}
