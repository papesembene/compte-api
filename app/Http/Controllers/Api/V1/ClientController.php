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
        $clients = $this->clientService->getClients($request->all());

        return $this->paginatedResponse($clients, 'Clients récupérés avec succès.');
    }

    public function store(StoreClientRequest $request): JsonResponse
    {
        $client = $this->clientService->createClient($request->validated());

        return $this->successResponse($client, 'Client créé avec succès.', 201);
    }

    public function show(Client $client): JsonResponse
    {
        return $this->successResponse($client, 'Client récupéré avec succès.');
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
