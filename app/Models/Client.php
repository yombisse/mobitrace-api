<?php

namespace App\Models;

use App\Models\Concerns\UsesUuid;
use App\Services\CnibNumberHasher;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
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
    'date_delivrance_piece',
    'date_expiration_piece',
])]
class Client extends Model
{
    use HasFactory, SoftDeletes, UsesUuid;

    protected $table = 'clients';

    protected static function booted(): void
    {
        static::saving(function (self $client): void {
            $number = $client->numero_piece;
            $client->numero_piece_hash = $number === null
                ? null
                : app(CnibNumberHasher::class)->hash($number);
        });
    }

    protected function casts(): array
    {
        return [
            'numero_piece' => 'encrypted',
            'date_naissance' => 'date',
            'date_delivrance_piece' => 'date',
            'date_expiration_piece' => 'date',
            'deleted_at' => 'datetime',
        ];
    }

    public function scopeWhereNumeroPiece(Builder $query, string $numeroPiece): Builder
    {
        return $query->where(
            'numero_piece_hash',
            app(CnibNumberHasher::class)->hash($numeroPiece),
        );
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
