<?php

namespace App\Http\Controllers;

use App\Http\Resources\TransactionHistoryResource;
use App\Models\Transaction;
use App\Services\TransactionHistoryService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class TransactionHistoryController extends Controller
{
    public function __construct(private readonly TransactionHistoryService $historyService) {}

    public function index(Request $request, Transaction $transaction): AnonymousResourceCollection
    {
        return TransactionHistoryResource::collection(
            $this->historyService->list($request->user(), $transaction),
        );
    }
}
