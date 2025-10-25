<?php

namespace App\Events;

use App\Models\Transaction;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Event déclenché lorsqu'une transaction est validée.
 *
 * Responsabilité : Notifier que la transaction a été créée et validée.
 */
class TransactionValidated
{
    use Dispatchable, SerializesModels;

    public Transaction $transaction;

    /**
     * Create a new event instance.
     */
    public function __construct(Transaction $transaction)
    {
        $this->transaction = $transaction;
    }
}
