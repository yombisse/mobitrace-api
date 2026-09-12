<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;

class UpdateProfileRequest extends ApiRequest
{
    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'string', 'max:255'],
            'email' => [
                'sometimes',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($this->user()->getKey()),
            ],
            'photo' => ['sometimes', 'nullable', 'string', 'max:2048'],
            'localisation_point' => ['sometimes', 'nullable', 'string', 'max:255'],
        ];
    }
}
