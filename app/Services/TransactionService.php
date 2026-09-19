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
            // Vérifier que le client a confirmé
            if (! isset($data['client_confirme']) || $data['client_confirme'] !== true) {
                throw new ApiException('La confirmation du client est requise pour créer une transaction.', 422);
            }

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

            // Générer le récapitulatif automatiquement
            $typeOperation = $data['type_operation'] === 'depot' ? 'Dépôt' : 'Retrait';
            $recap = "{$typeOperation} de {$data['montant']} FCFA pour le {$data['telephone']} via {$reseau->nom}";

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
                'consentement_recap' => $recap,
                'consentement_methode' => 'confirmation_client',
                'consentement_confirme_le' => now(),
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

        // Filtre par téléphone
        if (! empty($filters['telephone'])) {
            $query->whereHas('client', fn (Builder $builder) => $builder->where('telephone', $filters['telephone']));
        }

        // Filtre par recherche (nom/prénoms/téléphone)
        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $query->whereHas('client', function (Builder $builder) use ($search): void {
                $builder
                    ->where('nom', 'ilike', "%{$search}%")
                    ->orWhere('prenoms', 'ilike', "%{$search}%")
                    ->orWhere('telephone', 'ilike', "%{$search}%");
            });
        }

        // Filtre par référence
        if (! empty($filters['reference'])) {
            $query->where('reference', $filters['reference']);
        }

        // Filtre par réseau (ID)
        if (! empty($filters['reseau_id'])) {
            $query->where('reseau_id', $filters['reseau_id']);
        }

        // Filtre par réseau (code)
        if (! empty($filters['reseau_code'])) {
            $reseau = Reseau::whereRaw('LOWER(code) = ?', [strtolower($filters['reseau_code'])])->first();
            if ($reseau) {
                $query->where('reseau_id', $reseau->id);
            }
        }

        // Filtre par type d'opération
        if (! empty($filters['type_operation'])) {
            $query->where('type_operation', $filters['type_operation']);
        }

        // Filtre par statut
        if (! empty($filters['statut'])) {
            $query->where('statut', $filters['statut']);
        }

        // Filtre par montant (tranche min/max)
        if (! empty($filters['montant_min'])) {
            $query->where('montant', '>=', $filters['montant_min']);
        }

        if (! empty($filters['montant_max'])) {
            $query->where('montant', '<=', $filters['montant_max']);
        }

        // Filtre par période (prédéfinies)
        if (! empty($filters['periode']) && $filters['periode'] !== 'personnalisee') {
            $this->applyPeriodeFilter($query, $filters['periode']);
        } else {
            // Filtre par période personnalisée (date_debut/date_fin)
            if (! empty($filters['date_debut'])) {
                $query->whereDate('created_at', '>=', $filters['date_debut']);
            }

            if (! empty($filters['date_fin'])) {
                $query->whereDate('created_at', '<=', $filters['date_fin']);
            }
        }

        // Calculer le résumé avant le tri (excluant les transactions ANNULEE)
        $summaryQuery = clone $query;
        $summary = $this->calculateSummary($summaryQuery);

        // Tri personnalisable
        $sortBy = $filters['sort_by'] ?? 'created_at';
        $sortOrder = $filters['sort_order'] ?? 'desc';

        // Mapper les valeurs de sort_by aux champs de la base de données
        $sortByMapping = [
            'date' => 'created_at',
            'amount' => 'montant',
            'created_at' => 'created_at',
            'montant' => 'montant',
        ];

        $sortBy = $sortByMapping[$sortBy] ?? 'created_at';

        // Sécuriser l'ordre : seulement asc et desc
        if (! in_array($sortOrder, ['asc', 'desc'])) {
            $sortOrder = 'desc';
        }

        $query->orderBy($sortBy, $sortOrder)->orderBy('id', $sortOrder);

        $paginator = $query->paginate($this->perPage($filters['per_page'] ?? null));

        // Attacher le résumé au paginator
        $paginator->summary = $summary;

        return $paginator;
    }

    private function calculateSummary(Builder $query): array
    {
        // Exclure les transactions ANNULEE du résumé
        $query->where('statut', '!=', 'ANNULEE');

        $count = $query->count();
        $total = $query->sum('montant');

        return [
            'count' => $count,
            'total' => $total,
        ];
    }

    private function applyPeriodeFilter(Builder $query, string $periode): void
    {
        $now = now();

        switch ($periode) {
            case 'aujourdhui':
                $query->whereDate('created_at', $now->toDateString());
                break;

            case '7jours':
                $query->whereDate('created_at', '>=', $now->copy()->subDays(7)->toDateString());
                break;

            case 'cemois':
                $query->whereYear('created_at', $now->year)
                    ->whereMonth('created_at', $now->month);
                break;

            case 'personnalisee':
                // Ne rien faire, les dates personnalisées sont gérées après
                break;
        }
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
