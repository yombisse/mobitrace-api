<?php

namespace App\Services;

use App\Models\Reseau;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;

class DashboardService
{
    /**
     * @return array{date: string, nombre_transactions: int, total_depots: string, total_retraits: string, dernieres_transactions: Collection, derniers_soldes: array<int, array{reseau: array{id: string, nom: string, code: string}, solde: string|null}>}
     */
    public function summary(User $user): array
    {
        $today = now();
        $startOfDay = $today->copy()->startOfDay();
        $endOfDay = $today->copy()->endOfDay();
        $todayQuery = $this->todayQuery($user, $startOfDay, $endOfDay);
        $totals = (clone $todayQuery)
            ->where('statut', '<>', 'ANNULEE')
            ->selectRaw("count(*) as nombre_transactions, coalesce(sum(case when type_operation = 'depot' then montant else 0 end), 0) as total_depots, coalesce(sum(case when type_operation = 'retrait' then montant else 0 end), 0) as total_retraits")
            ->first();

        return [
            'date' => $today->toDateString(),
            'nombre_transactions' => (int) $totals->nombre_transactions,
            'total_depots' => $this->money($totals->total_depots),
            'total_retraits' => $this->money($totals->total_retraits),
            'dernieres_transactions' => $todayQuery
                ->with(['client', 'reseau'])
                ->latest('created_at')
                ->limit(5)
                ->get(),
            'derniers_soldes' => $this->latestBalances($user),
        ];
    }

    private function todayQuery(User $user, Carbon $startOfDay, Carbon $endOfDay)
    {
        return Transaction::query()
            ->where('user_id', $user->getKey())
            ->whereBetween('created_at', [$startOfDay, $endOfDay]);
    }

    /**
     * @return array<int, array{reseau: array{id: string, nom: string, code: string}, solde: string|null}>
     */
    private function latestBalances(User $user): array
    {
        return Reseau::withTrashed()
            ->orderBy('nom')
            ->get()
            ->map(function (Reseau $reseau) use ($user): array {
                $latestBalance = Transaction::query()
                    ->where('user_id', $user->getKey())
                    ->where('reseau_id', $reseau->getKey())
                    ->whereNotNull('solde_apres_operation')
                    ->latest('created_at')
                    ->value('solde_apres_operation');

                return [
                    'reseau' => [
                        'id' => $reseau->id,
                        'nom' => $reseau->nom,
                        'code' => $reseau->code,
                    ],
                    'solde' => $latestBalance === null ? null : $this->money($latestBalance),
                ];
            })
            ->all();
    }

    private function money(mixed $value): string
    {
        return number_format((float) $value, 2, '.', '');
    }
}
