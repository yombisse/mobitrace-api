<?php

namespace App\Models;

use App\Models\Concerns\UsesUuid;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['user_id', 'reseau_id', 'actif', 'solde_float', 'solde_cash'])]
class AgentReseau extends Model
{
    use HasFactory, UsesUuid;

    protected $table = 'agent_reseaux';

    protected function casts(): array
    {
        return [
            'actif' => 'boolean',
            'solde_float' => 'decimal:2',
            'solde_cash' => 'decimal:2',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function reseau(): BelongsTo
    {
        return $this->belongsTo(Reseau::class);
    }

    public function caisseSessions(): HasMany
    {
        return $this->hasMany(CaisseSession::class);
    }

    public function ajustements(): HasMany
    {
        return $this->hasMany(AjustementCaisse::class);
    }
}
