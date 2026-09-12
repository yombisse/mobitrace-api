<?php

namespace App\Models;

use App\Models\Concerns\UsesUuid;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['nom', 'code', 'logo'])]
class Reseau extends Model
{
    use HasFactory, SoftDeletes, UsesUuid;

    public $timestamps = false;

    protected $table = 'reseaux';

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }
}
