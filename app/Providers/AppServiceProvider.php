<?php

namespace App\Providers;

use App\Billing\PaymentGateway;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use App\HashidsTicketCodeGenerator;
use Laravel\Dusk\DuskServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Schema::defaultStringLength(191);
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {

        if ($this->app->environment('local', 'testing')) {
            $this->app->register(DuskServiceProvider::class);
        }
        $this->app->bind(StripePaymentGateway::class,  function () {
            return new StripePaymentGateway(config('services.stripe.key'));
        });

        $this->app->bind(PaymentGateway::class, StripePaymentGateway::class);
        $this->app->bind(\App\OrderConfirmationNumberGenerator::class, \App\RandomOrderConfirmationNumberGenerator::class);

        $this->app->bind(HashidsTicketCodeGenerator::class, function () {
            return new HashidsTicketCodeGenerator(config('app.ticket_code_salt'));
        });

        $this->app->bind(\App\TicketCodeGenerator::class, \App\HashidsTicketCodeGenerator::class);
    }
}
