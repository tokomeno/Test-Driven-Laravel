<?php

namespace Tests\Feature;

use App\User;
use App\Concert;
use Carbon\Carbon;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class AddConcertsTest extends TestCase
{
    use DatabaseMigrations;


    /** @test */
    public function poromters_can_view_the_add_concerts_form()
    {
        $user = factory(User::class)->create();;

        $this->withoutExceptionHandling();

        $response = $this->actingAs($user)->get('/backstage/concerts/new');

        $response->assertStatus(200);
    }

    /** @test */
    public function guest_can_not_view_the_add_concerts_form()
    {


        $response = $this->get('/backstage/concerts/new');

        $response->assertRedirect("/login");
    }

    /** @test */
    function adding_a_valid_concert()
    {
        $this->withoutExceptionHandling();

        $user = factory(User::class)->create();

        $response = $this->actingAs($user)->post('/backstage/concerts', [
            'title' => 'No Warning',
            'subtitle' => 'with Cruel Hand and Backtrack',
            'additional_information' => "You must be 19 years of age to attend this concert.",
            'date' => '2017-11-18',
            'time' => '8:00pm',
            'venue' => 'The Mosh Pit',
            'venue_address' => '123 Fake St.',
            'city' => 'Laraville',
            'state' => 'ON',
            'zip' => '12345',
            'ticket_price' => '32.50',
            'ticket_quantity' => '75',
        ]);

        tap(Concert::first(), function ($concert) use ($response, $user) {
            // $response->assertRedirect('/backstage/concerts');
            $response->assertRedirect(route('concerts.show', $concert));

            $this->assertTrue($concert->user->is($user));

            $this->assertFalse($concert->isPublished());

            $this->assertEquals('No Warning', $concert->title);
            $this->assertEquals('with Cruel Hand and Backtrack', $concert->subtitle);
            $this->assertEquals("You must be 19 years of age to attend this concert.", $concert->additional_information);
            $this->assertEquals(Carbon::parse('2017-11-18 8:00pm'), $concert->date);
            $this->assertEquals('The Mosh Pit', $concert->venue);
            $this->assertEquals('123 Fake St.', $concert->venue_address);
            $this->assertEquals('Laraville', $concert->city);
            $this->assertEquals('ON', $concert->state);
            $this->assertEquals('12345', $concert->zip);
            $this->assertEquals(3250, $concert->ticket_price);
            $this->assertEquals(75, $concert->ticket_quantity);
            // TODO:
            // $this->assertEquals(75, $concert->ticketsRemaining());
        });
    }


    /** @test */
    function guest_can_not_add_new_concerts()
    {
        // $this->withoutExceptionHandling();

        $response = $this->post('/backstage/concerts', [
            'title' => 'No Warning',
            'subtitle' => 'with Cruel Hand and Backtrack',
            'additional_information' => "You must be 19 years of age to attend this concert.",
            'date' => '2017-11-18',
            'time' => '8:00pm',
            'venue' => 'The Mosh Pit',
            'venue_address' => '123 Fake St.',
            'city' => 'Laraville',
            'state' => 'ON',
            'zip' => '12345',
            'ticket_price' => '32.50',
            'ticket_quantity' => '75',
        ]);

        $response->assertStatus(302);
        $response->assertRedirect('/login');
        $this->assertEquals(0, Concert::count());
    }
}
