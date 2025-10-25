<?php

namespace App\Services;

use App\Contracts\SmsNotifierInterface;
use App\Models\Client;
use Illuminate\Support\Facades\Log;

/**
 * Service pour l'envoi de SMS.
 *
 * Responsabilité : Gérer l'envoi de SMS avec le code d'authentification.
 * Respecte le principe de Dependency Inversion en dépendant de l'abstraction.
 */
class SmsService
{
    /**
     * Service de notification SMS injectable.
     */
    private SmsNotifierInterface $smsNotifier;

    /**
     * Constructeur avec injection de dépendance.
     *
     * @param SmsNotifierInterface $smsNotifier Service de notification SMS
     */
    public function __construct(SmsNotifierInterface $smsNotifier)
    {
        $this->smsNotifier = $smsNotifier;
    }

    /**
     * Envoie un SMS avec le code d'authentification.
     *
     * Utilise le service de notification injecté pour respecter le DIP.
     *
     * @param Client $client Le client destinataire
     * @param string $code Le code d'authentification
     * @return bool Succès de l'envoi
     */
    public function sendAuthenticationSms(Client $client, string $code): bool
    {
        $message = "Votre code d'authentification: {$code}";

        Log::info("Tentative d'envoi SMS d'authentification à {$client->telephone}");

        $result = $this->smsNotifier->send($client->telephone, $message);

        if ($result) {
            Log::info("SMS d'authentification envoyé avec succès à {$client->telephone}");
        } else {
            Log::error("Échec de l'envoi du SMS d'authentification à {$client->telephone}");
        }

        return $result;
    }
}