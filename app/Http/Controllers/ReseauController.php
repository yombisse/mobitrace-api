<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreReseauRequest;
use App\Http\Requests\UpdateReseauRequest;
use App\Http\Resources\ReseauResource;
use App\Models\Reseau;
use App\Services\ReseauService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ReseauController extends Controller
{
    public function __construct(private readonly ReseauService $reseauService) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $withDeleted = $request->boolean('avec_supprimes');

        return ReseauResource::collection($this->reseauService->list($withDeleted));
    }

    public function store(StoreReseauRequest $request): JsonResponse
    {
        $reseau = $this->reseauService->create($request->validated());

        return ApiResponse::success(
            ReseauResource::make($reseau)->resolve($request),
            'Réseau créé avec succès.',
            201,
        );
    }

    public function update(UpdateReseauRequest $request, Reseau $reseau): JsonResponse
    {
        $reseau = $this->reseauService->update($reseau, $request->validated());

        return ApiResponse::success(
            ReseauResource::make($reseau)->resolve($request),
            'Réseau modifié avec succès.',
        );
    }

    public function destroy(Reseau $reseau): JsonResponse
    {
        $this->reseauService->delete($reseau);

        return ApiResponse::success(null, 'Réseau supprimé avec succès.');
    }
}
