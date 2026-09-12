<?php

namespace App\Services;

use App\Exceptions\ApiException;
use App\Models\Client;
use App\Models\Reseau;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class TransactionService
{
    public function __construct(private readonly ReferenceGeneratorService $referenceGenerator) {}

    /**
     * @return array{transaction: Transaction, client: Client, client_existant: bool}
     */
    public function create(User $user, array $data): array
    {
        return DB::transaction(function () use ($user, $data): array {
            $reseau = Reseau::query()->findOrFail($data['reseau_id']);
            $client = Client::withTrashed()
                ->where('user_id', $user->getKey())
                ->where('telephone', $data['telephone'])
                ->first();
            $clientExistant = $client !== null;

            if ($client === null) {
                $client = new Client([
                    'user_id' => $user->getKey(),
                    ...$this->clientData($data),
                ]);
                $client->save();
            } else {
                if ($client->trashed()) {
                    $client->restore();
                }

                $this->completeClient($client, $data);
            }

            $transaction = $client->transactions()->create([
                'user_id' => $user->getKey(),
                'reseau_id' => $reseau->getKey(),
                'type_operation' => $data['type_operation'],
                'montant' => $data['montant'],
                'reference' => $this->referenceGenerator->generate($reseau),
                'solde_apres_operation' => $data['solde_apres_operation'] ?? null,
                'note' => $data['note'] ?? null,
                'statut' => 'ENREGISTREE',
                'version' => 1,
            ]);

            $this->recordHistory(
                $transaction,
                $user,
                'CREATION',
                null,
                $this->snapshot($transaction),
            );

            return [
                'transaction' => $transaction->load(['client', 'reseau']),
                'client' => $client->refresh(),
                'client_existant' => $clientExistant,
            ];
        });
    }

    public function paginate(User $user, array $filters): LengthAwarePaginator
    {
        $query = $this->ownedQuery($user)->with(['client', 'reseau', 'user']);

        if (! empty($filters['telephone'])) {
            $query->whereHas('client', fn (Builder $builder) => $builder->where('telephone', $filters['telephone']));
        }

        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $query->whereHas('client', function (Builder $builder) use ($search): void {
                $builder
                    ->where('nom', 'ilike', "%{$search}%")
                    ->orWhere('prenoms', 'ilike', "%{$search}%");
            });
        }

        foreach (['reference', 'reseau_id', 'type_operation', 'statut'] as $field) {
            if (! empty($filters[$field])) {
                $query->where($field, $filters[$field]);
            }
        }

        if (! empty($filters['date_debut'])) {
            $query->whereDate('created_at', '>=', $filters['date_debut']);
        }

        if (! empty($filters['date_fin'])) {
            $query->whereDate('created_at', '<=', $filters['date_fin']);
        }

        return $query
            ->latest('created_at')
            ->paginate($this->perPage($filters['per_page'] ?? null));
    }

    public function findOwned(User $user, Transaction $transaction): Transaction
    {
        if ($transaction->user_id !== $user->getKey()) {
            throw new ApiException('Ressource introuvable.', 404);
        }

        return $transaction->load(['client', 'reseau', 'user']);
    }

    public function update(User $user, Transaction $transaction, array $data): Transaction
    {
        return DB::transaction(function () use ($user, $transaction, $data): Transaction {
            $transaction = $this->findOwned($user, $transaction);
            $this->ensureEditable($transaction);
            $motif = $data['motif'];
            unset($data['motif']);
            $oldData = $this->snapshot($transaction, array_keys($data));

            $transaction->fill($data);
            $transaction->statut = 'MODIFIEE';
            $transaction->version++;
            $transaction->save();

            $this->recordHistory(
                $transaction,
                $user,
                'MODIFICATION',
                $oldData,
                $this->snapshot($transaction, array_keys($data)),
                $motif,
            );

            return $transaction->refresh()->load(['client', 'reseau', 'user']);
        });
    }

    public function cancel(User $user, Transaction $transaction, string $motif): Transaction
    {
        return DB::transaction(function () use ($user, $transaction, $motif): Transaction {
            $transaction = $this->findOwned($user, $transaction);
            $this->ensureEditable($transaction);
            $oldData = $this->snapshot($transaction, ['statut', 'version']);

            $transaction->statut = 'ANNULEE';
            $transaction->version++;
            $transaction->save();

            $this->recordHistory(
                $transaction,
                $user,
                'ANNULATION',
                $oldData,
                $this->snapshot($transaction, ['statut', 'version']),
                $motif,
            );

            return $transaction->refresh()->load(['client', 'reseau', 'user']);
        });
    }

    private function completeClient(Client $client, array $data): void
    {
        foreach ($this->clientData($data) as $field => $value) {
            if (blank($client->{$field}) && filled($value)) {
                $client->{$field} = $value;
            }
        }

        if ($client->isDirty()) {
            $client->save();
        }
    }

    private function clientData(array $data): array
    {
        return [
            'telephone' => $data['telephone'],
            'nom' => $data['nom'] ?? null,
            'prenoms' => $data['prenoms'] ?? null,
            'date_naissance' => $data['date_naissance'] ?? null,
            'nationalite' => $data['nationalite'] ?? null,
            'type_piece' => $data['type_piece'] ?? null,
            'numero_piece' => $data['numero_piece'] ?? null,
            'date_expiration_piece' => $data['date_expiration_piece'] ?? null,
        ];
    }

    private function ensureEditable(Transaction $transaction): void
    {
        if ($transaction->created_at->copy()->addHours(24)->isPast()) {
            throw new ApiException('Cette transaction ne peut plus être modifiée.', 422);
        }

        if ($transaction->statut === 'ANNULEE') {
            throw new ApiException('Cette transaction est déjà annulée.', 422);
        }
    }

    private function recordHistory(
        Transaction $transaction,
        User $user,
        string $action,
        ?array $oldData,
        ?array $newData,
        ?string $motif = null,
    ): void {
        $transaction->histories()->create([
            'user_id' => $user->getKey(),
            'action' => $action,
            'anciennes_donnees' => $oldData,
            'nouvelles_donnees' => $newData,
            'motif' => $motif,
        ]);
    }

    private function snapshot(Transaction $transaction, ?array $fields = null): array
    {
        $fields ??= ['type_operation', 'montant', 'reference', 'solde_apres_operation', 'note', 'statut', 'version'];

        return collect($fields)->mapWithKeys(fn (string $field) => [$field => $transaction->{$field}])->all();
    }

    private function ownedQuery(User $user): Builder
    {
        return Transaction::query()->where('user_id', $user->getKey());
    }

    private function perPage(int|string|null $perPage): int
    {
        return min(max((int) ($perPage ?: 20), 1), 100);
    }
}
