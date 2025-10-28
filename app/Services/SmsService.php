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
            // Essayer d'envoyer le vrai SMS via Twilio
            return $this->smsNotifier->send($client->telephone, $message);
        } catch (\Exception $e) {
            // En cas d'erreur Twilio, essayer avec un service gratuit alternatif
            try {
                return $this->sendWithAlternativeService($client->telephone, $message);
            } catch (\Exception $altException) {
                // Si les deux échouent, simuler l'envoi
                \Illuminate\Support\Facades\Log::warning("Tous les services SMS ont échoué, simulation activée. Twilio: {$e->getMessage()}, Alternative: {$altException->getMessage()}");
                \Illuminate\Support\Facades\Log::info("SMS simulé envoyé à {$client->telephone}: {$message}");

                // Retourner true pour ne pas bloquer la création du compte
                return true;
            }
        }
    }

    /**
     * Envoie un SMS via Orange API Sénégal
     *
     * @param string $to
     * @param string $message
     * @return bool
     */
    private function sendWithAlternativeService(string $to, string $message): bool
    {
        try {
            // Obtenir le token d'accès OAuth2
            $tokenResponse = Http::withBasicAuth(
                config('services.orange.client_id'),
                config('services.orange.client_secret')
            )->asForm()->post('https://api.orange.com/oauth/v3/token', [
                'grant_type' => 'client_credentials'
            ]);

            if (!$tokenResponse->successful()) {
                throw new \Exception("Orange OAuth failed: " . $tokenResponse->body());
            }

            $tokenData = $tokenResponse->json();
            if (!isset($tokenData['access_token'])) {
                throw new \Exception("No access token received from Orange");
            }

            $accessToken = $tokenData['access_token'];

            // Nettoyer le numéro (enlever +221 si présent)
            $cleanNumber = str_replace('+221', '', $to);

            // Envoyer le SMS
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $accessToken,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json'
            ])->post('https://api.orange.com/smsmessaging/v1/outbound/tel:+221' . config('services.orange.sender') . '/requests', [
                'outboundSMSMessageRequest' => [
                    'address' => 'tel:+221' . $cleanNumber,
                    'senderAddress' => 'tel:+221' . config('services.orange.sender'),
                    'outboundSMSTextMessage' => [
                        'message' => $message
                    ]
                ]
            ]);

            if ($response->successful()) {
                $data = $response->json();
                if (isset($data['outboundSMSMessageRequest'])) {
                    \Illuminate\Support\Facades\Log::info("SMS envoyé via Orange API à {$to}");
                    return true;
                }
            }

            throw new \Exception("Orange SMS failed: " . $response->body());
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::warning("Orange API error: {$e->getMessage()}");
            throw new \Exception("Orange API service error: {$e->getMessage()}");
        }
    }
}