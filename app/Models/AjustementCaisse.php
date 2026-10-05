<?php

namespace App\Models;

use App\Models\Concerns\UsesUuid;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['agent_reseau_id', 'type', 'montant_float', 'montant_cash', 'motif'])]
class AjustementCaisse extends Model
{
    use HasFactory, UsesUuid;

    protected $table = 'ajustements_caisse';

    protected function casts(): array
    {
        return [
            'montant_float' => 'decimal:2',
            'montant_cash' => 'decimal:2',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function agentReseau(): BelongsTo
    {
        return $this->belongsTo(AgentReseau::class);
    }
}
