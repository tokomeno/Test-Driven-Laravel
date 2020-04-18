<?php

namespace Tests;

use Mail;
use Mockery;
use Tests\CreatesApplication;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;


    protected function setUp()
    {
        parent::setUp();

        Mockery::getConfiguration()->allowMockingNonExistentMethods(false);
        Mail::fake();
    }
}




// trait CreatesApplication
// {
//     /**
//      * Creates the application.
//      *
//      * @return \Illuminate\Foundation\Application
//      */
//     public function createApplication()
//     {
//         $app = require __DIR__.'/../bootstrap/app.php';

//         $app->make(Kernel::class)->bootstrap();

//         return $app;
//     }
// }
