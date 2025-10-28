<?php

namespace App\Services;

use App\Models\Client;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Service pour la gestion des clients.
 *
 * Responsabilité : Encapsuler la logique métier des clients.
 */
class ClientService
{
    /**
     * Récupère une liste paginée de clients avec options de recherche et tri.
     */
    public function getClients(array $params): LengthAwarePaginator
    {
        $query = Client::query();

        if (isset($params['search'])) {
            $search = $params['search'];
            $query->where('titulaire', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
        }

        $sort = $params['sort'] ?? 'created_at';
        $order = $params['order'] ?? 'desc';

        return $query->orderBy($sort, $order)->paginate($params['limit'] ?? 10);
    }

    /**
     * Trouve un client existant ou en crée un nouveau avec password et code.
     *
     * @param array $data
     * @return Client
     */
    public function findOrCreateClient(array $data): Client
    {
        // Vérifier si le client existe par email ou téléphone
        $client = Client::where('email', $data['email'])
                        ->orWhere('telephone', $data['telephone'])
                        ->first();

        if ($client) {
            return $client;
        }

        // Générer password et code avant création
        $plainPassword = Client::generatePassword(); // Stocker en variable séparée
        $data['password'] = $plainPassword; // Le modèle va hasher automatiquement
        $data['code'] = Client::generateCode();

        // Créer un nouveau client avec password et code générés
        $client = Client::create($data);

        // Attacher le mot de passe en clair pour l'envoi d'email
        $client->plain_password = $plainPassword;

        return $client;
    }

    /**
     * Crée un nouveau client.
     */
    public function createClient(array $data): Client
    {
        return Client::create($data);
    }

    /**
     * Met à jour un client.
     */
    public function updateClient(Client $client, array $data): Client
    {
        $client->update($data);
        return $client;
    }

    /**
     * Supprime un client (soft delete).
     */
    public function deleteClient(Client $client): void
    {
        $client->delete();
    }
}