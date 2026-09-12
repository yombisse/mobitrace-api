<?php

namespace App\Http\Controllers;

use App\Http\Requests\CancelTransactionRequest;
use App\Http\Requests\ListTransactionRequest;
use App\Http\Requests\StoreTransactionRequest;
use App\Http\Requests\UpdateTransactionRequest;
use App\Http\Resources\TransactionResource;
use App\Models\Transaction;
use App\Services\TransactionService;
use App\Support\ApiResponse;
use App\Support\Pagination;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    public function __construct(private readonly TransactionService $transactionService) {}

    public function store(StoreTransactionRequest $request): JsonResponse
    {
        $result = $this->transactionService->create($request->user(), $request->validated());

        return ApiResponse::success([
            'transaction' => TransactionResource::make($result['transaction'])->resolve($request),
            'client' => [
                'id' => $result['client']->id,
                'telephone' => $result['client']->telephone,
                'nom' => $result['client']->nom,
                'prenoms' => $result['client']->prenoms,
            ],
            'client_existant' => $result['client_existant'],
        ], 'Transaction enregistrée avec succès.', 201);
    }

    public function index(ListTransactionRequest $request): JsonResponse
    {
        $paginator = $this->transactionService->paginate($request->user(), $request->validated());

        return ApiResponse::success(
            TransactionResource::collection($paginator->getCollection())->resolve($request),
            'Opération effectuée avec succès.',
            200,
            Pagination::meta($paginator),
        );
    }

    public function show(Request $request, Transaction $transaction): TransactionResource
    {
        return TransactionResource::make(
            $this->transactionService->findOwned($request->user(), $transaction),
        );
    }

    public function update(UpdateTransactionRequest $request, Transaction $transaction): TransactionResource
    {
        return TransactionResource::make(
            $this->transactionService->update($request->user(), $transaction, $request->validated()),
        );
    }

    public function cancel(CancelTransactionRequest $request, Transaction $transaction): TransactionResource
    {
        return TransactionResource::make(
            $this->transactionService->cancel(
                $request->user(),
                $transaction,
                $request->string('motif')->toString(),
            ),
        );
    }
}
