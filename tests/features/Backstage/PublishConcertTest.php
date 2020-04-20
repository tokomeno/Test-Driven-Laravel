<?php

namespace Tests\Feature\Backstage;

use App\User;
use App\Concert;
use Carbon\Carbon;
use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class PublishConcertTest extends TestCase
{
    use DatabaseMigrations;

    /** @test */
    public function a_promoter_can_publish_their_own_concert()
    {
        $this->withoutExceptionHandling();

        /** @var User $user */
        $user = factory(User::class)->create();

        /** @var Concert $concert */
        $concert = factory(Concert::class)->states('unpublished')->create(['user_id' => $user->id, 'ticket_quantity' => 3]);

        $this->assertFalse($concert->isPublished());

        $response = $this->actingAs($user)->from("/backstage/concerts")->post(route("backstage.publishedconcert.store", [
            'concert_id' => $concert->id
        ]));

        $response->assertRedirect('/backstage/concerts');

        $concert = $concert->fresh();


        $this->assertTrue($concert->isPublished());
        $this->assertEquals($concert->ticketsRemaining(), 3);
    }


    /** @test */
    public function a_concert_only_be_published_once()
    {
        // $this->withoutExceptionHandling();

        /** @var User $user */
        $user = factory(User::class)->create();

        /** @var Concert $concert */
        $concert =  \ConcertFactory::createPublished([
            'user_id' => $user->id,
            'ticket_quantity' => 3
        ]);

        $this->assertTrue($concert->isPublished());

        $response = $this->actingAs($user)->from("/backstage/concerts")->post(route("backstage.publishedconcert.store", [
            'concert_id' => $concert->id
        ]));

        $response->assertStatus(422);

        $concert = $concert->fresh();


        $this->assertTrue($concert->isPublished());
        $this->assertEquals($concert->ticketsRemaining(), 3);
    }
}
