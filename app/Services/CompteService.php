<?php

namespace App\Services;

use App\Models\Compte;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Service pour la gestion des comptes.
 *
 * Responsabilité : Encapsuler la logique métier des comptes.
 */
class CompteService
{
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
     * Crée un nouveau compte.
     */
    public function createCompte(array $data): Compte
    {
        return Compte::create($data);
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