<?php

namespace Tests\Feature;

use App\User;
use App\Concert;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class AddConcertsTest extends TestCase
{
    use DatabaseMigrations;


    /** @test */
    public function poromters_can_view_the_add_concerts_form()
    {
        $user = factory(User::class)->create();

        $this->withExceptionHandling();

        $response = $this->actingAs($user)->get('/backstage/concerts/new');

        $response->assertStatus(200);
    }

    /** @test */
    public function guest_can_not_view_the_add_concerts_form()
    {


        $response = $this->get('/backstage/concerts/new');

        $response->assertRedirect("/login");
    }
}
