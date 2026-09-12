<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rules\Password;

class ResetPasswordRequest extends ApiRequest
{
    public function rules(): array
    {
        return [
            'email' => ['required', 'email', 'max:255'],
            'code' => ['required', 'digits:6'],
            'password' => ['required', 'confirmed', Password::min(8)],
        ];
    }
}
