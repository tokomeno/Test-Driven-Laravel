<?php

namespace Tests\Unit;

use Mockery;
use App\Ticket;
use Tests\TestCase;
use App\HashidsTicketCodeGenerator;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class HashidsTicketCodeGeneratorTest extends TestCase
{
    use DatabaseMigrations;

    /** @test */
    public function ticket_codes_are_at_least_6_char_long()
    {
        $ticketCodeGenerator = new HashidsTicketCodeGenerator;

        $code = $ticketCodeGenerator->generateFor(factory(Ticket::class)->make(['id' => 1]));

        $this->assertTrue(strlen($code) >= 6);
    }


    /** @test */
    public function ticket_code_can_only_contain_upper_Case()
    {
        $ticketCodeGenerator = new HashidsTicketCodeGenerator;

        $code = $ticketCodeGenerator->generateFor(factory(Ticket::class)->make(['id' => 1]));

        $this->assertRegExp('/^[A-Z]+$/', $code);
    }


    /** @test */
    public function ticket_codes_for_same_id_are_samenction()
    {
        $ticketCodeGenerator = new HashidsTicketCodeGenerator;

        $code1 = $ticketCodeGenerator->generateFor(factory(Ticket::class)->make(['id' => 1]));
        $code2 = $ticketCodeGenerator->generateFor(factory(Ticket::class)->make(['id' => 1]));
        
        $this->assertEquals($code1, $code2);
    }
    

    /** @test */
    public function ticket_codes_not_same_id_are_not_samenction()
    {
        $ticketCodeGenerator = new HashidsTicketCodeGenerator;
 
        $code1 = $ticketCodeGenerator->generateFor(factory(Ticket::class)->make(['id' => 1]));
        $code2 = $ticketCodeGenerator->generateFor(factory(Ticket::class)->make(['id' => 2]));
        $this->assertNotEquals($code1, $code2);
    }


    /** @test */
    public function ticket_codes_genetated_with_diff()
    {
        $ticketCodeGenerator = new HashidsTicketCodeGenerator;
 
        $code1 = $ticketCodeGenerator->generateFor(factory(Ticket::class)->make(['id' => 1]));
        $code2 = $ticketCodeGenerator->generateFor(factory(Ticket::class)->make(['id' => 2]));
         
        $this->assertNotEquals($code1, $code2);
    }
}
