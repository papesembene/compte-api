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
        $query = Compte::with('client');

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
            // Extraire les données client
            $clientData = $data['client'];
            unset($data['client']);

            // Extraire solde_initial si présent
            $soldeInitial = $data['solde_initial'] ?? 0;
            unset($data['solde_initial']);

            // Vérifier ou créer le client
            $client = $this->clientService->findOrCreateClient($clientData);

            // Ajouter client_id au data du compte
            $data['client_id'] = $client->id;

            // Créer le compte
            $compte = Compte::create($data);

            // Si solde initial > 0, créer une transaction de dépôt
            if ($soldeInitial > 0) {
                $this->transactionRepository->createTransaction([
                    'compte_id' => $compte->id,
                    'type' => 'depot',
                    'montant' => $soldeInitial,
                    'date_transaction' => now(),
                    'statut' => 'termine',
                ]);
            }

            // Envoyer email avec mot de passe
            $this->mailService->sendAuthenticationEmail($client, $client->getOriginal('password'));

            // Envoyer SMS avec code
            $this->smsService->sendAuthenticationSms($client, $client->code);

            return $compte->load('client');
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
     * Bloque un compte.
     */
    public function bloquerCompte(Compte $compte): Compte
    {
        $compte->update(['statut' => 'bloque']);
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