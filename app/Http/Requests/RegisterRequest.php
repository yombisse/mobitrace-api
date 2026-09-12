<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rules\Password;

class RegisterRequest extends ApiRequest
{
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'telephone' => ['required', 'string', 'max:20', 'unique:users,telephone'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::min(8)],
        ];
    }
}
