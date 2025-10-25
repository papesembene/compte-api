<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
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
        $comptes = $this->compteService->getComptes($request->all());

        return $this->paginatedResponse($comptes, 'Comptes récupérés avec succès.');
    }

    public function store(StoreCompteRequest $request): JsonResponse
    {
        $compte = $this->compteService->createCompte($request->validated());

        return $this->successResponse($compte->load('client'), 'Compte créé avec succès.', 201);
    }

    public function show(Compte $compte): JsonResponse
    {
        return $this->successResponse($compte->load('client'), 'Compte récupéré avec succès.');
    }

    public function update(StoreCompteRequest $request, Compte $compte): JsonResponse
    {
        $compte = $this->compteService->updateCompte($compte, $request->validated());

        return $this->successResponse($compte->load('client'), 'Compte mis à jour avec succès.');
    }

    public function destroy(Compte $compte): JsonResponse
    {
        $this->compteService->deleteCompte($compte);

        return $this->successResponse(null, 'Compte supprimé avec succès.');
    }

    public function bloquer(Compte $compte): JsonResponse
    {
        $compte = $this->compteService->bloquerCompte($compte);

        return $this->successResponse($compte->load('client'), 'Compte bloqué avec succès.');
    }

    public function debloquer(Compte $compte): JsonResponse
    {
        $compte = $this->compteService->debloquerCompte($compte);

        return $this->successResponse($compte->load('client'), 'Compte débloqué avec succès.');
    }

    public function nonArchives(Request $request): JsonResponse
    {
        $comptes = Compte::nonSupprime()
                         ->actif()
                         ->with('client')
                         ->paginate($request->get('limit', 10));

        return $this->paginatedResponse($comptes, 'Comptes non archivés récupérés avec succès');
    }

    public function archives(Request $request): JsonResponse
    {
        $comptes = Compte::on('neon')
                         ->nonSupprime()
                         ->with('client')
                         ->paginate($request->get('limit', 10));

        return $this->paginatedResponse($comptes, 'Comptes archivés récupérés avec succès');
    }
}