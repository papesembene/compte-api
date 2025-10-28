<?php

namespace App\Services;

use App\Mail\AuthenticationMail;
use App\Models\Client;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

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
        try {
            Mail::to($client->email)->send(new AuthenticationMail($client, $password));

            Log::info("Email d'authentification envoyé avec succès à {$client->email}");

            return true;
        } catch (\Exception $e) {
            Log::error("Échec de l'envoi de l'email d'authentification à {$client->email}: " . $e->getMessage());

            return false;
        }
    }
}