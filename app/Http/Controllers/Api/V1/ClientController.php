<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreClientRequest;
use App\Models\Client;
use App\Services\ClientService;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class ClientController extends Controller
{
    use ApiResponseTrait;

    private ClientService $clientService;

    public function __construct(ClientService $clientService)
    {
        $this->clientService = $clientService;
    }

    public function index(Request $request): JsonResponse
    {
        $user = auth()->user();

        // Vérifier si c'est un admin
        if (!$this->isAdmin($user)) {
            return $this->errorResponse('Accès refusé. Réservé aux administrateurs.', 403);
        }

        $clients = $this->clientService->getClients($request->all());

        return $this->paginatedResponse($clients, 'Clients récupérés avec succès.');
    }

    public function store(StoreClientRequest $request): JsonResponse
    {
        $client = $this->clientService->createClient($request->validated());

        return $this->successResponse($client, 'Client créé avec succès.', 201);
    }

    public function show(string $identifier): JsonResponse
    {
        $user = auth()->user();

        // Trouver le client par NCI ou téléphone
        $client = Client::findByIdentifier($identifier)->first();

        if (!$client) {
            return $this->errorResponse('Client non trouvé.', 404);
        }

        // Vérifier si c'est un admin
        if (!$this->isAdmin($user)) {
            return $this->errorResponse('Accès refusé. Réservé aux administrateurs.', 403);
        }

        return $this->successResponse($client->load('comptes'), 'Client récupéré avec succès.');
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

    public function update(StoreClientRequest $request, Client $client): JsonResponse
    {
        $client = $this->clientService->updateClient($client, $request->validated());

        return $this->successResponse($client, 'Client mis à jour avec succès.');
    }

    public function destroy(Client $client): JsonResponse
    {
        $this->clientService->deleteClient($client);

        return $this->successResponse(null, 'Client supprimé avec succès.');
    }
}
