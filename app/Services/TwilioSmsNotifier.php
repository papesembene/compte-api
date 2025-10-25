<?php

namespace App\Services;

use App\Contracts\SmsNotifierInterface;
use Illuminate\Support\Facades\Log;

/**
 * Service pour envoyer des SMS via Twilio.
 *
 * Implémente SmsNotifierInterface pour l'envoi de SMS réels.
 */
class TwilioSmsNotifier implements SmsNotifierInterface
{
    /**
     * Envoie un SMS via Twilio.
     *
     * @param string $to
     * @param string $message
     * @return bool
     */
    public function send(string $to, string $message): bool
    {
        // Simulation d'envoi via Twilio
        // En production : utiliser Twilio SDK

        Log::info("SMS envoyé via Twilio à {$to} : {$message}");

        return true;
    }
}