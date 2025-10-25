<?php

namespace App\Services;

use App\Contracts\SmsNotifierInterface;
use Illuminate\Support\Facades\Log;
use Twilio\Rest\Client;

/**
 * Service pour envoyer des SMS via Twilio.
 *
 * Implémente SmsNotifierInterface pour l'envoi de SMS réels.
 */
class TwilioSmsNotifier implements SmsNotifierInterface
{
    protected Client $twilio;

    /**
     * Constructeur pour initialiser le client Twilio.
     */
    public function __construct()
    {
        $this->twilio = new Client(
            config('services.twilio.sid'),
            config('services.twilio.token')
        );
    }

    /**
     * Envoie un SMS via Twilio.
     *
     * @param string $to
     * @param string $message
     * @return bool
     */
    public function send(string $to, string $message): bool
    {
        try {
            $this->twilio->messages->create(
                $to,
                [
                    'from' => config('services.twilio.from'),
                    'body' => $message,
                ]
            );

            Log::info("SMS envoyé via Twilio à {$to} : {$message}");

            return true;
        } catch (\Exception $e) {
            Log::error("Erreur lors de l'envoi du SMS via Twilio : " . $e->getMessage());

            return false;
        }
    }
}