<?php

namespace App\Models;

use App\Models\Concerns\UsesUuid;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'user_id',
    'client_id',
    'reseau_id',
    'caisse_session_id',
    'type_operation',
    'montant',
    'reference',
    'solde_apres_operation',
    'note',
    'statut',
    'sync_status',
    'version',
    'synced_at',
    'consentement_recap',
    'consentement_methode',
    'consentement_confirme_le',
])]
class Transaction extends Model
{
    use HasFactory, UsesUuid;

    protected $table = 'transactions';

    protected static function booted()
    {
        static::updating(function ($transaction) {
            // Empêcher la modification de caisse_session_id après création
            if ($transaction->isDirty('caisse_session_id')) {
                throw new \Exception('caisse_session_id ne peut pas être modifié après création.');
            }
        });
    }

    protected function casts(): array
    {
        return [
            'montant' => 'decimal:2',
            'solde_apres_operation' => 'decimal:2',
            'version' => 'integer',
            'synced_at' => 'datetime',
            'consentement_confirme_le' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function reseau(): BelongsTo
    {
        return $this->belongsTo(Reseau::class);
    }

    public function caisseSession(): BelongsTo
    {
        return $this->belongsTo(CaisseSession::class);
    }

    public function histories(): HasMany
    {
        return $this->hasMany(TransactionHistory::class);
    }
}
