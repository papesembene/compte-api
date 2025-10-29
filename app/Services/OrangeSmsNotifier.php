<?php

namespace App\Services;

use App\Contracts\SmsNotifierInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Service pour envoyer des SMS via Orange API Sénégal.
 *
 * Implémente SmsNotifierInterface pour l'envoi de SMS via Orange.
 */
class OrangeSmsNotifier implements SmsNotifierInterface
{
    /**
     * Envoie un SMS via Orange API.
     *
     * @param string $to
     * @param string $message
     * @return bool
     */
    public function send(string $to, string $message): bool
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
                    Log::info("SMS envoyé via Orange API à {$to}");
                    return true;
                }
            }

            throw new \Exception("Orange SMS failed: " . $response->body());
        } catch (\Exception $e) {
            Log::error("Erreur lors de l'envoi du SMS via Orange API : " . $e->getMessage());
            return false;
        }
    }
}