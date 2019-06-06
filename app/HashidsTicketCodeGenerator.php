<?php

namespace App;

class HashidsTicketCodeGenerator implements TicketCodeGenerator
{
    private $hashids;

    public function __construct()
    {
        $this->hashids = new \Hashids\Hashids('sdasdsa', 6, "ABCDEFGHIJKLMNOPQRSTUVWXYZ");
    }

    public function generateFor($ticket)
    {
        return $this->hashids->encode($ticket->id);
    }
}
