<?php

namespace App\Models;

use App\Models\Concerns\UsesUuid;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'agent_reseau_id',
    'date',
    'solde_float_ouverture',
    'solde_cash_ouverture',
    'solde_float_theorique',
    'solde_cash_theorique',
    'solde_float_reel',
    'solde_cash_reel',
    'ecart_float',
    'ecart_cash',
    'justification',
    'statut',
    'ouverte_at',
    'cloturee_at',
])]
class CaisseSession extends Model
{
    use HasFactory, UsesUuid;

    protected $table = 'caisse_sessions';

    public $timestamps = true;

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'solde_float_ouverture' => 'decimal:2',
            'solde_cash_ouverture' => 'decimal:2',
            'solde_float_theorique' => 'decimal:2',
            'solde_cash_theorique' => 'decimal:2',
            'solde_float_reel' => 'decimal:2',
            'solde_cash_reel' => 'decimal:2',
            'ecart_float' => 'decimal:2',
            'ecart_cash' => 'decimal:2',
            'ouverte_at' => 'datetime',
            'cloturee_at' => 'datetime',
        ];
    }

    public function agentReseau(): BelongsTo
    {
        return $this->belongsTo(AgentReseau::class);
    }
}
