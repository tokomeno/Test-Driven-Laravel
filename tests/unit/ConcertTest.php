<?php

use App\Concert;
use App\Exceptions\NotEnoughTicketsException;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Tests\TestCase;

class ConcertTest extends TestCase
{
    use DatabaseMigrations;

    /** @test */
    public function can_get_formatted_date()
    {
        $concert = factory(Concert::class)->make([
            'date' => Carbon::parse('2016-12-01 8:00pm'),
        ]);

        $this->assertEquals('December 1, 2016', $concert->formatted_date);
    }

    /** @test */
    public function can_get_formatted_start_time()
    {
        $concert = factory(Concert::class)->make([
            'date' => Carbon::parse('2016-12-01 17:00:00'),
        ]);

        $this->assertEquals('5:00pm', $concert->formatted_start_time);
    }

    /** @test */
    public function can_get_ticket_price_in_dollars()
    {
        $concert = factory(Concert::class)->make([
            'ticket_price' => 6750,
        ]);

        $this->assertEquals('67.50', $concert->ticket_price_in_dollars);
    }

    /** @test */
    public function concerts_with_a_published_at_date_are_published()
    {
        $publishedConcertA = factory(Concert::class)->create(['published_at' => Carbon::parse('-1 week')]);
        $publishedConcertB = factory(Concert::class)->create(['published_at' => Carbon::parse('-1 week')]);
        $unpublishedConcert = factory(Concert::class)->create(['published_at' => null]);

        $publishedConcerts = Concert::published()->get();

        $this->assertTrue($publishedConcerts->contains($publishedConcertA));
        $this->assertTrue($publishedConcerts->contains($publishedConcertB));
        $this->assertFalse($publishedConcerts->contains($unpublishedConcert));
    }


    /** @test */
    public function can_add_tickets()
    {
        $concert = factory(Concert::class)->create();

        $concert->addTickets(50);

        $this->assertEquals(50, $concert->ticketsRemaining());
    }


    /** @test */
    public function tickets_reamening_does_not_include_ticket_asoc_to_order()
    {
        $concert = factory(Concert::class)->create();

        $concert->tickets()->saveMany(
            factory('App\Ticket', 3)->create(['order_id' => 1])
        );
        $concert->tickets()->saveMany(
            factory('App\Ticket', 2)->create(['order_id' => null])
        );

        //act
        $this->assertEquals(2, $concert->ticketsRemaining());
    }

    /** @test */
    public function ticket_sold_only_inlcudes_assosication_with_an_order()
    {
        $concert = factory(Concert::class)->create();

        $concert->tickets()->saveMany(
            factory('App\Ticket', 3)->create(['order_id' => 1])
        );
        $concert->tickets()->saveMany(
            factory('App\Ticket', 2)->create(['order_id' => null])
        );

        $this->assertEquals(3, $concert->ticketsSold());
    }

    /** @test */
    public function tring_to_reserve_more_tickets_than_remain_throws_an_excaption()
    {
        $concert = factory(Concert::class)->create()->addTickets(10);
        try {
            $reservation = $concert->reserveTickets(30, 'tok@gmail.com');
        } catch (NotEnoughTicketsException $e) {
            $orders = $concert->orders()->where('email', 'tok@gmail.com')->get();
            $this->assertEmpty($orders);
            $this->assertEquals(10, $concert->ticketsRemaining());
            return;
        }
        $this->fail("Order Succeced even no more tickets left");
    }


    /** @test */
    // public function cannot_order_tickets_that_have_already_been_purcased()
    // {
    //    $concert = factory(Concert::class)->create();
    //    $concert->addTickets(8);
    //    $concert->orderTickets('zuka@gmail.com', 7);

    //     try {
    //         $concert->orderTickets('tok@gmail.com', 3);
    //    } catch (NotEnoughTicketsException $e) {
    //        $tokoOrders =  $concert->orders()->where('email', 'tok@gmail.com')->first();
    //        $this->assertNull($tokoOrders);
    //        $this->assertEquals(1,  $concert->ticketsRemaining());
    //         return;
    //    }
    //     $this->fail("cannot order tickets_that_have_already_been_purcased");
    // }


    /** @test */
    public function can_reserve_aviable_tickets()
    {
        $concert = factory(Concert::class)->create()->addTickets(3);

        $this->assertEquals(3, $concert->ticketsRemaining());

        $reservation = $concert->reserveTickets(2, 'tok@mail.com');

        $this->assertCount(2, $reservation->tickets());
        $this->assertEquals('tok@mail.com', $reservation->email());

        $this->assertEquals(1, $concert->ticketsRemaining());
    }

    /** @test */
    public function cannot_reserve_tickets_which_was_already_purchased()
    {
        // $concert = factory(Concert::class)->create()->addTickets(3);

        //  $concert->orderTickets('tokomeno@mial.com', 2);

        $concert = factory(Concert::class)->create()->addTickets(3);
        $order = factory('App\Order')->create();
        $order->tickets()->saveMany($concert->tickets->take(2));

        try {
            $concert->reserveTickets(2, 'tokomeno@mial.com');
        } catch (NotEnoughTicketsException $e) {
            $this->assertEquals(1, $concert->ticketsRemaining());
            return;
        }

        $this->fails('cannot_reserve_tickets_which_was_already_purchased');
    }

    /** @test */
    public function cannot_reserve_tickets_which_was_already_reserved()
    {
        $concert = factory(Concert::class)->create()->addTickets(3);

        $concert->reserveTickets(2, 'tokomeno@mial.com');

        try {
            $concert->reserveTickets(2, 'tokomeno@mial.com');
        } catch (NotEnoughTicketsException $e) {
            $this->assertEquals(1, $concert->ticketsRemaining());
            return;
        }

        $this->fails('cannot_reserve_tickets_which_was_already_reserved');
    }

    /** @test */
    public function concerts_can_be_published()
    {
        /** @var Concert $concert  */
        $concert = factory(Concert::class)->create(['published_at' => null, 'ticket_quantity' => 5]);

        $this->assertFalse($concert->isPublished());


        $concert->publish();

        $this->assertTrue($concert->isPublished());
        $this->assertEquals($concert->ticketsRemaining(), 5);
    }
}
