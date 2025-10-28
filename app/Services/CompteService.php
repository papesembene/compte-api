<?php

namespace App\Services;

use App\Models\Compte;
use App\Models\Transaction;
use App\Repositories\TransactionRepositoryManager;
use App\Services\ClientService;
use App\Services\MailService;
use App\Services\SmsService;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

/**
 * Service pour la gestion des comptes.
 *
 * Responsabilité : Encapsuler la logique métier des comptes.
 */
class CompteService
{
    private TransactionRepositoryManager $transactionRepository;
    private ClientService $clientService;
    private MailService $mailService;
    private SmsService $smsService;

    public function __construct(
        TransactionRepositoryManager $transactionRepository,
        ClientService $clientService,
        MailService $mailService,
        SmsService $smsService
    ) {
        $this->transactionRepository = $transactionRepository;
        $this->clientService = $clientService;
        $this->mailService = $mailService;
        $this->smsService = $smsService;
    }
    /**
     * Récupère une liste paginée de comptes avec options de filtre et tri.
     */
    public function getComptes(array $params): LengthAwarePaginator
    {
        $query = Compte::with('client')->nonSupprime(); 

        if (isset($params['type'])) {
            $query->where('type_compte', $params['type']);
        }

        if (isset($params['client_id'])) {
            $query->where('client_id', $params['client_id']);
        }

        if (isset($params['statut'])) {
            $query->where('statut', $params['statut']);
        }

        $sort = $params['sort'] ?? 'created_at';
        $order = $params['order'] ?? 'desc';

        return $query->orderBy($sort, $order)->paginate($params['limit'] ?? 10);
    }

    /**
     * Crée un nouveau compte avec vérification/création du client, solde initial, et envoi de notifications.
     */
    public function createCompte(array $data): Compte
    {
        return DB::transaction(function () use ($data) {
            try {
                // Extraire les données client
                $clientData = $data['client'];
                unset($data['client']);

                // Vérifier ou créer le client
                $client = $this->clientService->findOrCreateClient($clientData);

                // Ajouter client_id au data du compte
                $data['client_id'] = $client->id;

                // Créer le compte (le numéro est généré automatiquement dans le boot du modèle)
                $compte = Compte::create($data);

                // Le solde est calculé dynamiquement, pas besoin de solde_initial

                // Envoyer email avec mot de passe
                $plainPassword = $client->plain_password ?? \App\Models\Client::generatePassword(); // Utiliser le mot de passe stocké ou en générer un nouveau
                $mailResult = $this->mailService->sendAuthenticationEmail($client, $plainPassword);

                // Envoyer SMS avec code (ne pas faire échouer la transaction si SMS échoue)
                try {
                    $this->smsService->sendAuthenticationSms($client, $client->code);
                } catch (\Exception $e) {
                    // Log l'erreur SMS mais ne pas faire échouer la transaction
                    \Illuminate\Support\Facades\Log::warning('SMS sending failed but transaction continues: ' . $e->getMessage());
                }

                return $compte->load('client');
            } catch (\Exception $e) {
                // Log l'erreur détaillée
                \Illuminate\Support\Facades\Log::error('Error in createCompte: ' . $e->getMessage(), [
                    'data' => $data,
                    'trace' => $e->getTraceAsString()
                ]);
                throw $e; // Re-throw pour que la transaction rollback
            }
        });
    }

    /**
     * Met à jour un compte.
     */
    public function updateCompte(Compte $compte, array $data): Compte
    {
        $compte->update($data);
        return $compte;
    }

    /**
     * Supprime un compte (soft delete).
     */
    public function deleteCompte(Compte $compte): void
    {
        $compte->delete();
    }

    /**
     * Bloque un compte avec dates optionnelles.
     */
    public function bloquerCompte(Compte $compte, array $data = []): Compte
    {
        $updateData = ['statut' => 'bloque'];

        if (isset($data['date_debut_blocage'])) {
            $updateData['date_debut_blocage'] = $data['date_debut_blocage'];
        }

        if (isset($data['date_fin_blocage'])) {
            $updateData['date_fin_blocage'] = $data['date_fin_blocage'];
        }

        $compte->update($updateData);
        return $compte;
    }

    /**
     * Débloque un compte.
     */
    public function debloquerCompte(Compte $compte): Compte
    {
        $compte->update(['statut' => 'debloque']);
        return $compte;
    }
}