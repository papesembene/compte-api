<?php

namespace App\Listeners;

use App\Contracts\SmsNotifierInterface;
use App\Events\TransactionValidated;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

/**
 * Listener pour envoyer une notification SMS lors de la validation d'une transaction.
 *
 * Responsabilité : Envoyer un SMS de confirmation au client.
 */
class SendTransactionNotification implements ShouldQueue
{
    use InteractsWithQueue;

    private SmsNotifierInterface $smsNotifier;

    /**
     * Create the event listener.
     */
    public function __construct(SmsNotifierInterface $smsNotifier)
    {
        $this->smsNotifier = $smsNotifier;
    }

    /**
     * Handle the event.
     */
    public function handle(TransactionValidated $event): void
    {
        $transaction = $event->transaction;
        $client = $transaction->client; // Utilise l'accessor getClientAttribute

        if ($client && $client->telephone) {
            $message = "Transaction {$transaction->type} de {$transaction->montant} FCFA effectuée avec succès sur le compte {$transaction->compte->numero_compte}.";

            $this->smsNotifier->send($client->telephone, $message);

            Log::info("Notification SMS envoyée pour transaction {$transaction->id}");
        }
    }
}
