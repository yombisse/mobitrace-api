<?php

namespace App\Models;

use App\Models\Concerns\UsesUuid;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Visible;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['email', 'code', 'expires_at', 'used_at'])]
#[Visible(['id', 'email', 'expires_at', 'used_at', 'created_at'])]
class PasswordResetCode extends Model
{
    use UsesUuid;

    public $timestamps = false;

    protected $table = 'password_reset_codes';

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'used_at' => 'datetime',
            'created_at' => 'datetime',
        ];
    }
}
