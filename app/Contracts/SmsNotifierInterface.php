<?php

namespace App\Contracts;

/**
 * Interface pour les services de notification SMS.
 *
 * Définit les méthodes pour envoyer des SMS, permettant le découplage
 * et l'extensibilité (ex. : Twilio, Fake, etc.).
 */
interface SmsNotifierInterface
{
    /**
     * Envoie un SMS à un numéro de téléphone.
     *
     * @param string $to
     * @param string $message
     * @return bool
     */
    public function send(string $to, string $message): bool;
}