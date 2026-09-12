<?php

namespace App\Services;

use App\Exceptions\ApiException;
use App\Models\Client;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class ClientService
{
    public function paginate(User $user, array $filters): LengthAwarePaginator
    {
        $query = $this->ownedQuery($user)
            ->withCount('transactions')
            ->withMax('transactions', 'created_at')
            ->orderBy('nom')
            ->orderBy('prenoms');

        if (! empty($filters['telephone'])) {
            $query->where('telephone', $filters['telephone']);
        }

        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function (Builder $builder) use ($search): void {
                $builder
                    ->where('telephone', 'ilike', "%{$search}%")
                    ->orWhere('nom', 'ilike', "%{$search}%")
                    ->orWhere('prenoms', 'ilike', "%{$search}%");
            });
        }

        return $query->paginate($this->perPage($filters['per_page'] ?? null));
    }

    public function lookup(User $user, string $telephone): ?Client
    {
        return $this->ownedQuery($user)
            ->where('telephone', $telephone)
            ->first();
    }

    public function findOwned(User $user, Client $client): Client
    {
        if ($client->user_id !== $user->getKey()) {
            throw new ApiException('Ressource introuvable.', 404);
        }

        return $client;
    }

    /**
     * @return array{nombre_transactions: int, total_depots: string, total_retraits: string}
     */
    public function statistics(Client $client): array
    {
        $statistics = $client->transactions()
            ->where('statut', '<>', 'ANNULEE')
            ->selectRaw("count(*) as nombre_transactions, coalesce(sum(case when type_operation = 'depot' then montant else 0 end), 0) as total_depots, coalesce(sum(case when type_operation = 'retrait' then montant else 0 end), 0) as total_retraits")
            ->first();

        return [
            'nombre_transactions' => (int) $statistics->nombre_transactions,
            'total_depots' => number_format((float) $statistics->total_depots, 2, '.', ''),
            'total_retraits' => number_format((float) $statistics->total_retraits, 2, '.', ''),
        ];
    }

    public function transactions(User $user, Client $client, int|string|null $perPage): LengthAwarePaginator
    {
        $this->findOwned($user, $client);

        return $client->transactions()
            ->with(['client', 'reseau'])
            ->latest('created_at')
            ->paginate($this->perPage($perPage));
    }

    public function update(User $user, Client $client, array $data): Client
    {
        $client = $this->findOwned($user, $client);
        $client->fill($data);
        $client->save();

        return $client->refresh();
    }

    private function ownedQuery(User $user): Builder
    {
        return Client::query()->where('user_id', $user->getKey());
    }

    private function perPage(int|string|null $perPage): int
    {
        return min(max((int) ($perPage ?: 20), 1), 100);
    }
}
