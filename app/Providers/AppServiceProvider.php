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
        // Enregistrer les bindings pour SmsNotifierInterface
        $this->app->bind(\App\Contracts\SmsNotifierInterface::class, function ($app) {
            // Utiliser FakeSmsNotifier en développement, TwilioSmsNotifier en production
            return app(\App\Services\FakeSmsNotifier::class);
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Enregistrer l'observer pour Transaction
        Transaction::observe(TransactionObserver::class);

        // Enregistrer le listener pour TransactionValidated event
        Event::listen(
            TransactionValidated::class,
            SendTransactionNotification::class
        );
    }
}
