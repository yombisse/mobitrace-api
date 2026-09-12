<?php

namespace App\Models;

use App\Models\Concerns\UsesUuid;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'user_id',
    'telephone',
    'nom',
    'prenoms',
    'date_naissance',
    'nationalite',
    'type_piece',
    'numero_piece',
    'date_expiration_piece',
])]
class Client extends Model
{
    use HasFactory, SoftDeletes, UsesUuid;

    protected $table = 'clients';

    protected function casts(): array
    {
        return [
            'date_naissance' => 'date',
            'date_expiration_piece' => 'date',
            'deleted_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }
}
