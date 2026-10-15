<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rules\Password;

class ChangePasswordRequest extends ApiRequest
{
    public function rules(): array
    {
        return [
            'current_password' => ['required', 'string'],
            'password' => ['required', 'confirmed', Password::min(6)->mixedCase()->numbers()->symbols()],
        ];
    }
}
