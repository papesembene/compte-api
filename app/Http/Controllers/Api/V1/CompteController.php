<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCompteRequest;
use App\Models\Compte;
use App\Services\CompteService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @OA\Tag(
 *     name="Comptes",
 *     description="Gestion des comptes bancaires"
 * )
 */
class CompteController extends Controller
{
    private CompteService $compteService;

    public function __construct(CompteService $compteService)
    {
        $this->compteService = $compteService;
    }

    public function index(Request $request): JsonResponse
    {
        $comptes = $this->compteService->getComptes($request->all());

        return response()->json([
            'data' => $comptes,
            'message' => 'Comptes récupérés avec succès.'
        ]);
    }

    public function store(StoreCompteRequest $request): JsonResponse
    {
        $compte = $this->compteService->createCompte($request->validated());

        return response()->json([
            'data' => $compte->load('client'),
            'message' => 'Compte créé avec succès.'
        ], 201);
    }

    public function show(Compte $compte): JsonResponse
    {
        return response()->json([
            'data' => $compte->load('client'),
            'message' => 'Compte récupéré avec succès.'
        ]);
    }

    public function update(StoreCompteRequest $request, Compte $compte): JsonResponse
    {
        $compte = $this->compteService->updateCompte($compte, $request->validated());

        return response()->json([
            'data' => $compte->load('client'),
            'message' => 'Compte mis à jour avec succès.'
        ]);
    }

    public function destroy(Compte $compte): JsonResponse
    {
        $this->compteService->deleteCompte($compte);

        return response()->json([
            'message' => 'Compte supprimé avec succès.'
        ]);
    }

    public function bloquer(Compte $compte): JsonResponse
    {
        $compte = $this->compteService->bloquerCompte($compte);

        return response()->json([
            'data' => $compte->load('client'),
            'message' => 'Compte bloqué avec succès.'
        ]);
    }

    public function debloquer(Compte $compte): JsonResponse
    {
        $compte = $this->compteService->debloquerCompte($compte);

        return response()->json([
            'data' => $compte->load('client'),
            'message' => 'Compte débloqué avec succès.'
        ]);
    }
}