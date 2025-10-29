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
use Illuminate\Support\Facades\DB;


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

        return $this->paginatedResponse($comptes->makeHidden(['client']), 'Comptes récupérés avec succès.');
    }

    public function store(StoreCompteRequest $request): JsonResponse
    {
        $compte = $this->compteService->createCompte($request->validated());

        return $this->successResponse($compte, 'Compte créé avec succès.', 201);
    }

    public function show(string $numero_compte): JsonResponse
    {
        $user = auth()->user();

        $result = $this->compteService->getCompteDetails($numero_compte, $user);

        if (isset($result['error'])) {
            return $this->errorResponse($result['error'], $result['code']);
        }

        return $this->successResponse($result['data'], $result['message']);
    }

    public function update(StoreCompteRequest $request, Compte $compte): JsonResponse
    {
        // Vérifier si le compte est supprimé ou bloqué
        if ($compte->trashed() || $compte->statut === 'bloque') {
            return $this->errorResponse('Impossible de modifier un compte supprimé ou bloqué.', 400);
        }

        $compte = $this->compteService->updateCompte($compte, $request->validated());

        return $this->successResponse($compte, 'Compte mis à jour avec succès.');
    }

    public function destroy(Compte $compte): JsonResponse
    {
        $result = $this->compteService->deleteCompte($compte);

        if (isset($result['error'])) {
            return $this->errorResponse($result['error'], $result['code']);
        }

        return $this->successResponse(null, $result['message']);
    }

    public function bloquer(BloquerCompteRequest $request, Compte $compte): JsonResponse
    {
        // Vérifier si le compte est supprimé
        if ($compte->trashed()) {
            return $this->errorResponse('Impossible de bloquer un compte supprimé.', 400);
        }

        // Vérifier que seul les comptes épargne actifs peuvent être bloqués
        if ($compte->type_compte !== 'epargne') {
            return $this->errorResponse('Seuls les comptes épargne peuvent être bloqués.', 400);
        }

        if ($compte->statut === 'bloque') {
            return $this->errorResponse('Ce compte est déjà bloqué.', 400);
        }

        $compte = $this->compteService->bloquerCompte($compte, $request->validated());

        // Ajouter les informations de blocage à la réponse
        $responseData = $compte->toArray();
        $responseData['date_debut_blocage'] = $compte->date_debut_blocage;
        $responseData['date_fin_blocage'] = $compte->date_fin_blocage;

        return $this->successResponse($responseData, 'Compte bloqué avec succès.');
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
