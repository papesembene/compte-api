<?php

namespace App\Services;

use App\Contracts\SmsNotifierInterface;
use Illuminate\Support\Facades\Log;

/**
 * Service pour simuler l'envoi de SMS (pour tests/développement).
 *
 * Implémente SmsNotifierInterface pour l'envoi de SMS fictifs.
 */
class FakeSmsNotifier implements SmsNotifierInterface
{
    /**
     * Simule l'envoi d'un SMS.
     *
     * @param string $to
     * @param string $message
     * @return bool
     */
    public function send(string $to, string $message): bool
    {
        // Simulation d'envoi de SMS
        Log::info("SMS fictif envoyé à {$to} : {$message}");

        return true;
    }
}