<?php

namespace App\Models;

use App\Models\Concerns\UsesUuid;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'transaction_id',
    'user_id',
    'action',
    'anciennes_donnees',
    'nouvelles_donnees',
    'motif',
])]
class TransactionHistory extends Model
{
    use HasFactory, UsesUuid;

    public const UPDATED_AT = null;

    protected $table = 'transaction_histories';

    protected function casts(): array
    {
        return [
            'anciennes_donnees' => 'array',
            'nouvelles_donnees' => 'array',
            'created_at' => 'datetime',
        ];
    }

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
