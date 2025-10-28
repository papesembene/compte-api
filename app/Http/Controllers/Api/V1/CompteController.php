<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\BloquerCompteRequest;
use App\Http\Requests\StoreCompteRequest;
use App\Http\Resources\CompteResource;
use App\Models\Compte;
use App\Services\CompteService;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;


class CompteController extends Controller
{
    use ApiResponseTrait;

    private CompteService $compteService;

    public function __construct(CompteService $compteService)
    {
        $this->compteService = $compteService;
    }

    public function index(Request $request): JsonResponse
    {
        $user = auth()->user();

        $query = Compte::nonSupprime()
                       ->where('statut', '!=', 'bloque')
                       ->whereIn('type_compte', ['courant', 'epargne'])
                       ->with('client')
                       ->orderBy($request->get('sort', 'created_at'), $request->get('order', 'desc'));

        // Vérifier si c'est un admin ou un client
        if (!$this->isAdmin($user)) {
            // Client ne voit que ses propres comptes
            $query->where('client_id', $user->id);
        }

        $comptes = $query->paginate($request->get('limit', 10));

        return $this->paginatedResponse($comptes, 'Comptes récupérés avec succès.');
    }

    public function store(StoreCompteRequest $request): JsonResponse
    {
        $compte = $this->compteService->createCompte($request->validated());

        return $this->successResponse($compte->load('client'), 'Compte créé avec succès.', 201);
    }

    public function show(Compte $compte): JsonResponse
    {
        // Vérifier si le compte est supprimé ou bloqué
        if ($compte->trashed() || $compte->statut === 'bloque') {
            return $this->errorResponse('Compte non trouvé ou inaccessible.', 404);
        }

        $user = auth()->user();

        // Vérifier si c'est un admin ou un client
        if (!$this->isAdmin($user)) {
            // Client ne peut voir que ses propres comptes
            if ($compte->client_id !== $user->id) {
                return $this->errorResponse('Accès refusé. Vous ne pouvez voir que vos propres comptes.', 403);
            }
        }

        return $this->successResponse($compte->load('client'), 'Compte récupéré avec succès.');
    }

    public function update(StoreCompteRequest $request, Compte $compte): JsonResponse
    {
        // Vérifier si le compte est supprimé ou bloqué
        if ($compte->trashed() || $compte->statut === 'bloque') {
            return $this->errorResponse('Impossible de modifier un compte supprimé ou bloqué.', 400);
        }

        $compte = $this->compteService->updateCompte($compte, $request->validated());

        return $this->successResponse($compte->load('client'), 'Compte mis à jour avec succès.');
    }

    public function destroy(Compte $compte): JsonResponse
    {
        // Vérifier si le compte est déjà supprimé
        if ($compte->trashed()) {
            return $this->errorResponse('Le compte est déjà supprimé.', 400);
        }

        $this->compteService->deleteCompte($compte);

        return $this->successResponse(null, 'Compte supprimé avec succès.');
    }

    public function bloquer(BloquerCompteRequest $request, Compte $compte): JsonResponse
    {
        // Vérifier si le compte est supprimé
        if ($compte->trashed()) {
            return $this->errorResponse('Impossible de bloquer un compte supprimé.', 400);
        }

        // Vérifier que seul les comptes épargne peuvent être bloqués
        if ($compte->type_compte !== 'epargne') {
            return $this->errorResponse('Seuls les comptes épargne peuvent être bloqués.', 400);
        }

        $compte = $this->compteService->bloquerCompte($compte, $request->validated());

        return $this->successResponse($compte->load('client'), 'Compte bloqué avec succès.');
    }

    public function debloquer(Compte $compte): JsonResponse
    {
        // Vérifier si le compte est supprimé
        if ($compte->trashed()) {
            return $this->errorResponse('Impossible de débloquer un compte supprimé.', 400);
        }

        // Vérifier que seul les comptes épargne peuvent être débloqués
        if ($compte->type_compte !== 'epargne') {
            return $this->errorResponse('Seuls les comptes épargne peuvent être débloqués.', 400);
        }

        $compte = $this->compteService->debloquerCompte($compte);

        return $this->successResponse($compte->load('client'), 'Compte débloqué avec succès.');
    }

    public function nonArchives(Request $request): JsonResponse
    {
        $user = auth()->user();

        $query = Compte::nonSupprime()
                       ->actif()
                       ->with('client')
                       ->orderBy($request->get('sort', 'created_at'), $request->get('order', 'desc'));

        // Vérifier si c'est un admin ou un client
        if (!$this->isAdmin($user)) {
            // Client ne voit que ses propres comptes
            $query->where('client_id', $user->id);
        }

        $comptes = $query->paginate($request->get('limit', 10));

        return $this->paginatedResponse($comptes, 'Comptes actifs récupérés avec succès');
    }

    public function archives(Request $request): JsonResponse
    {
        $user = auth()->user();

        // Vérifier si c'est un admin
        if (!$this->isAdmin($user)) {
            return $this->errorResponse('Accès refusé. Réservé aux administrateurs.', 403);
        }

        $comptes = Compte::nonSupprime()
                         ->where('statut', 'bloque')
                         ->with('client')
                         ->orderBy($request->get('sort', 'created_at'), $request->get('order', 'desc'))
                         ->paginate($request->get('limit', 10));

        return $this->paginatedResponse($comptes, 'Comptes archivés récupérés avec succès');
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
}
