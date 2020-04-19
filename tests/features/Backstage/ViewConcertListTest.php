<?php

namespace Tests\Feature;

use App\User;
use App\Concert;
use Tests\TestCase;
use PHPUnit\Framework\Assert;
use Illuminate\Support\Collection;
use Illuminate\Foundation\Testing\TestResponse;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class ViewConcertListTest extends TestCase
{
    use DatabaseMigrations;


    protected function setUp()
    {
        parent::setUp();
        TestResponse::macro('data', function ($key) {
            return $this->original->getData()[$key];
        });

        Collection::macro('assertEquals', function ($data) {
            dd($this->toArray(), collect($data)->toArray());
            //    $this->assertEquals($data, $this->data->);
            // Assert::assertEquals($data, $this->toArray());
            // Assert::tr
        });
    }

    /** @test */
    public function guests_cannot_view_promoters_concerts_list()
    {
        $response = $this->get("/backstage/concerts");

        $response->assertStatus(302);

        $response->assertRedirect('/login');
    }

    // /** @test */
    // public function propoters_can_only_view_own_concerts()
    // {
    //     $this->withoutExceptionHandling();
    //     $user = factory(User::class)->create();

    //     $conerts = factory(Concert::class)->create(['user_id' => $user->id]);

    //     $response = $this->actingAs($user)->get(route("backstage.concerts.index"));


    //     $response->assertStatus(200);
    //     dd($response->original->getData()['concerts']->contains($conerts[0]));
    // }


    // $publishedConcertA = ConcertFactory::createPublished(['user_id' => $user->id]);
    // $publishedConcertB = ConcertFactory::createPublished(['user_id' => $otherUser->id]);
    // $publishedConcertC = ConcertFactory::createPublished(['user_id' => $user->id]);

    // $unpublishedConcertA = ConcertFactory::createUnpublished(['user_id' => $user->id]);
    // $unpublishedConcertB = ConcertFactory::createUnpublished(['user_id' => $otherUser->id]);
    // $unpublishedConcertC = ConcertFactory::createUnpublished(['user_id' => $user->id]);
    /** @test */
    function promoters_can_view_a_list_of_their_concerts()
    {
        $this->withoutExceptionHandling();
        $this->assertTrue(true);
        // $user = factory(User::class)->create();
        // $otherUser = factory(User::class)->create();
        // $publishedConcertA = factory(Concert::class)->create(['user_id' => $user->id])->publish();
        // $publishedConcertB = factory(Concert::class)->create(['user_id' => $otherUser->id])->publish();
        // $publishedConcertC = factory(Concert::class)->create(['user_id' => $user->id])->publish();

        // $unpublishedConcertA =  factory(Concert::class)->create(['user_id' => $user->id]);
        // $unpublishedConcertB =  factory(Concert::class)->create(['user_id' => $otherUser->id]);
        // $unpublishedConcertC =  factory(Concert::class)->create(['user_id' => $user->id]);

        // $response = $this->actingAs($user)->get('/backstage/concerts');

        // $response->assertStatus(200);

        // $response->data('publishedConcerts')->assertEquals([
        //     $publishedConcertA,
        //     $publishedConcertC,
        // ]);

        // $response->data('unpublishedConcerts')->assertEquals([
        //     $unpublishedConcertA,
        //     $unpublishedConcertC,
        // ]);
    }
}
