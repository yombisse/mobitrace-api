<?php

namespace App\Http\Controllers;

use App\Http\Requests\CancelTransactionRequest;
use App\Http\Requests\ExportTransactionRequest;
use App\Http\Requests\ListTransactionRequest;
use App\Http\Requests\StoreTransactionRequest;
use App\Http\Requests\UpdateTransactionRequest;
use App\Http\Resources\TransactionResource;
use App\Models\Transaction;
use App\Services\ReleveTransactionsPdfService;
use App\Services\TransactionService;
use App\Support\ApiResponse;
use App\Support\Pagination;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class TransactionController extends Controller
{
    public function __construct(
        private readonly TransactionService $transactionService,
        private readonly ReleveTransactionsPdfService $pdfService
    ) {}

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
            Pagination::meta($paginator, $paginator->summary ?? null),
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

    public function export(ExportTransactionRequest $request): \Symfony\Component\HttpFoundation\BinaryFileResponse|\Illuminate\Http\JsonResponse
    {
        try {
            $filepath = $this->pdfService->generate($request->user(), $request->validated());

            $filename = basename($filepath);

            return response()->file($filepath, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="'.$filename.'"',
            ])->deleteFileAfterSend(app()->environment('testing') ? false : true);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
