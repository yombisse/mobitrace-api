<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateClientRequest;
use App\Http\Resources\ClientResource;
use App\Http\Resources\ClientTransactionResource;
use App\Models\Client;
use App\Services\ClientService;
use App\Support\ApiResponse;
use App\Support\Pagination;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ClientController extends Controller
{
    public function __construct(private readonly ClientService $clientService) {}

    public function index(Request $request): JsonResponse
    {
        $paginator = $this->clientService->paginate($request->user(), $request->all());

        return ApiResponse::success(
            ClientResource::collection($paginator->getCollection())->resolve($request),
            'Opération effectuée avec succès.',
            200,
            Pagination::meta($paginator),
        );
    }

    public function lookup(Request $request): JsonResponse
    {
        $telephone = $request->validate([
            'telephone' => ['required', 'string', 'max:20'],
        ])['telephone'];
        $client = $this->clientService->lookup($request->user(), $telephone);

        return ApiResponse::success([
            'found' => $client !== null,
            'client' => $client === null ? null : [
                'id' => $client->id,
                'telephone' => $client->telephone,
                'nom' => $client->nom,
                'prenoms' => $client->prenoms,
            ],
        ]);
    }

    public function show(Request $request, Client $client): ClientResource
    {
        $client = $this->clientService->findOwned($request->user(), $client);
        $client->setAttribute('statistiques', $this->clientService->statistics($client));

        return ClientResource::make($client);
    }

    public function transactions(Request $request, Client $client): JsonResponse
    {
        $paginator = $this->clientService->transactions(
            $request->user(),
            $client,
            $request->query('per_page'),
        );

        return ApiResponse::success(
            ClientTransactionResource::collection($paginator->getCollection())->resolve($request),
            'Opération effectuée avec succès.',
            200,
            Pagination::meta($paginator),
        );
    }

    public function update(UpdateClientRequest $request, Client $client): ClientResource
    {
        return ClientResource::make(
            $this->clientService->update($request->user(), $client, $request->validated()),
        );
    }
}
