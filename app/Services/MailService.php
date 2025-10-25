<?php

namespace App\Services;

use App\Models\Client;
use Illuminate\Support\Facades\Log;

/**
 * Service pour l'envoi d'emails.
 *
 * Responsabilité : Gérer l'envoi d'emails d'authentification.
 */
class MailService
{
    /**
     * Envoie un email d'authentification avec le mot de passe.
     *
     * @param Client $client
     * @param string $password
     * @return bool
     */
    public function sendAuthenticationEmail(Client $client, string $password): bool
    {
        // Simulation d'envoi d'email
        // En production, utiliser Mail::to($client->email)->send(new AuthenticationMail($password));

        Log::info("Email d'authentification envoyé à {$client->email} avec mot de passe: {$password}");

        return true;
    }
}