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
        $query = Compte::nonSupprime();

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
     * Récupère les détails d'un compte spécifique avec gestion des comptes archivés.
     */
    public function getCompteDetails(string $numero_compte, $user)
    {
        // Trouver le compte par numéro
        $compte = Compte::numero($numero_compte)->nonSupprime()->first();

        if (!$compte) {
            return ['error' => 'Compte non trouvé.', 'code' => 404];
        }

        // Vérifier si le compte est bloqué
        if ($compte->statut === 'bloque') {
            // Si c'est un compte épargne bloqué, vérifier s'il est archivé dans Neon
            if ($compte->type_compte === 'epargne') {
                $archivedData = DB::connection('neon')
                    ->table('blocked_accounts')
                    ->where('numero_compte', $numero_compte)
                    ->first();

                if ($archivedData) {
                    // Retourner les données archivées
                    $compteData = json_decode($archivedData->compte_data, true);
                    $clientData = json_decode($archivedData->client_data, true);

                    // Ajouter les dates de blocage
                    $compteData['date_debut_blocage'] = $archivedData->date_debut_blocage;
                    $compteData['date_fin_blocage'] = $archivedData->date_fin_blocage;
                    $compteData['client'] = $clientData;

                    return ['data' => $compteData, 'message' => 'Détails du compte archivé récupérés avec succès.'];
                }
            }

            return ['error' => 'Compte non trouvé ou inaccessible.', 'code' => 404];
        }

        // Vérifier les permissions
        if (!$this->isAdmin($user)) {
            // Client ne peut voir que ses propres comptes
            if ($compte->client_id !== $user->id) {
                return ['error' => 'Accès refusé. Vous ne pouvez voir que vos propres comptes.', 'code' => 403];
            }
        }

        // Pour les comptes épargne, ajouter les dates de blocage si elles existent
        $responseData = $compte;
        if ($compte->type_compte === 'epargne' && ($compte->date_debut_blocage || $compte->date_fin_blocage)) {
            $responseData->date_debut_blocage = $compte->date_debut_blocage;
            $responseData->date_fin_blocage = $compte->date_fin_blocage;
        }

        return ['data' => $responseData, 'message' => 'Détails du compte récupérés avec succès.'];
    }

    /**
     * Vérifier si l'utilisateur est un admin
     */
    private function isAdmin($user): bool
    {
        // Pour l'instant, on considère que tous les utilisateurs authentifiés via User sont admins
        // et ceux via Client sont des clients normaux
        return $user instanceof \App\Models\User;
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
                try {
                    $mailResult = $this->mailService->sendAuthenticationEmail($client, $plainPassword);
                } catch (\Exception $e) {
                    // Log l'erreur email mais ne pas faire échouer la transaction
                    \Illuminate\Support\Facades\Log::warning('Email sending failed but transaction continues: ' . $e->getMessage());
                }

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
     * Supprime un compte (soft delete) - seulement si actif.
     */
    public function deleteCompte(Compte $compte): array
    {
        // Vérifier si le compte est déjà supprimé
        if ($compte->trashed()) {
            return ['error' => 'Le compte est déjà supprimé.', 'code' => 400];
        }

        // Vérifier si le compte est bloqué
        if ($compte->statut === 'bloque') {
            return ['error' => 'Impossible de supprimer un compte bloqué.', 'code' => 400];
        }

        $compte->delete();

        return ['success' => true, 'message' => 'Compte supprimé avec succès.'];
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

}