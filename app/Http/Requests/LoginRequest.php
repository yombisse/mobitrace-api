<?php

namespace App\Http\Requests;

class LoginRequest extends ApiRequest
{
    public function rules(): array
    {
        return [
            'telephone' => ['required', 'string', 'max:20'],
            'password' => ['required', 'string'],
        ];
    }
}
