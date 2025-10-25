<?php

namespace App\Services;

use App\Models\Client;
use Illuminate\Support\Facades\Log;

/**
 * Service pour l'envoi de SMS.
 *
 * Responsabilité : Gérer l'envoi de SMS avec le code d'authentification.
 */
class SmsService
{
    /**
     * Envoie un SMS avec le code d'authentification.
     *
     * @param Client $client
     * @param string $code
     * @return bool
     */
    public function sendAuthenticationSms(Client $client, string $code): bool
    {
        // Simulation d'envoi de SMS
        // En production, utiliser un service comme Twilio ou AWS SNS

        Log::info("SMS d'authentification envoyé à {$client->telephone} avec code: {$code}");

        return true;
    }
}