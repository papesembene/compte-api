<?php

namespace App\Jobs;

use App\Mail\AuthenticationMail;
use App\Models\Client;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Job pour envoyer l'email d'authentification de manière asynchrone.
 *
 * Responsabilité : Envoyer l'email d'authentification avec les identifiants
 * lors de la création d'un compte client.
 */
class SendAuthenticationEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected Client $client;
    protected string $password;

    /**
     * Create a new job instance.
     */
    public function __construct(Client $client, string $password)
    {
        $this->client = $client;
        $this->password = $password;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            Mail::to($this->client->email)->send(new AuthenticationMail($this->client, $this->password));

            Log::info("Email d'authentification envoyé avec succès à {$this->client->email} via Job");
        } catch (\Exception $e) {
            Log::error("Échec de l'envoi de l'email d'authentification à {$this->client->email} via Job: " . $e->getMessage());

            // Relancer l'exception pour marquer le job comme échoué
            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error("Job SendAuthenticationEmail échoué pour {$this->client->email}: " . $exception->getMessage());
    }
}
