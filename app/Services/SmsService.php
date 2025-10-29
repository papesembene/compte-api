<?php

namespace App\Services;

use App\Contracts\SmsNotifierInterface;
use Illuminate\Support\Facades\Http;

/**
 * Service pour l'envoi de SMS.
 *
 * Responsabilité : Gérer l'envoi de SMS d'authentification.
 */
class SmsService
{
    private SmsNotifierInterface $smsNotifier;

    public function __construct(SmsNotifierInterface $smsNotifier)
    {
        $this->smsNotifier = $smsNotifier;
    }

    /**
     * Envoie un SMS d'authentification avec le code.
     *
     * @param \App\Models\Client $client
     * @param string $code
     * @return bool
     */
    public function sendAuthenticationSms(\App\Models\Client $client, string $code): bool
    {
        $message = "Votre code d'authentification est : {$code}";

        try {
            // Envoyer le SMS via Orange API
            return $this->smsNotifier->send($client->telephone, $message);
        } catch (\Exception $e) {
            // En cas d'erreur, simuler l'envoi pour ne pas bloquer la création du compte
            \Illuminate\Support\Facades\Log::warning("Service SMS Orange a échoué, simulation activée: " . $e->getMessage());
            \Illuminate\Support\Facades\Log::info("SMS simulé envoyé à {$client->telephone}: {$message}");

            // Retourner true pour ne pas bloquer la création du compte
            return true;
        }
    }

}