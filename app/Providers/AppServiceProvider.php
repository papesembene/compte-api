<?php

namespace App\Providers;

use App\Events\TransactionValidated;
use App\Listeners\SendTransactionNotification;
use App\Models\Transaction;
use App\Observers\TransactionObserver;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Enregistrer le binding pour SmsService avec injection de dépendance
        $this->app->bind(\App\Contracts\SmsNotifierInterface::class, \App\Services\TwilioSmsNotifier::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
       

        // Enregistrer le listener pour TransactionValidated event
        Event::listen(
           
            SendTransactionNotification::class
        );
    }
}
