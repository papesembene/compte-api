<?php

namespace App\Jobs;

use App\Models\Transaction;
use App\Repositories\CloudTransactionRepository;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Job pour archiver les transactions locales vers Neon et supprimer les locales.
 *
 * Responsabilité : Archiver les transactions anciennes pour optimiser la DB locale.
 */
class ArchiveDailyTransactionsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(CloudTransactionRepository $cloudRepository): void
    {
        Log::info('Démarrage de l\'archivage des transactions vers Neon.');

        DB::transaction(function () use ($cloudRepository) {
            // Récupérer les transactions locales d'avant aujourd'hui
            $transactions = Transaction::whereDate('date_transaction', '<', today())->get();

            foreach ($transactions as $transaction) {
                // Archiver vers Neon
                $cloudRepository->createTransaction($transaction->toArray());

                // Supprimer de la DB locale
                $transaction->delete();
            }

            Log::info("Archivage terminé : {$transactions->count()} transactions archivées.");
        });
    }
}
