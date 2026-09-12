<?php

namespace App\Services;

use App\Exceptions\ApiException;
use App\Models\Transaction;
use App\Models\TransactionHistory;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class TransactionHistoryService
{
    public function list(User $user, Transaction $transaction): Collection
    {
        if ($transaction->user_id !== $user->getKey()) {
            throw new ApiException('Ressource introuvable.', 404);
        }

        return TransactionHistory::query()
            ->where('transaction_id', $transaction->getKey())
            ->with('user')
            ->orderBy('created_at')
            ->orderBy('id')
            ->get();
    }
}
