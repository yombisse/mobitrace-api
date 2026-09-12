<?php

namespace App\Http\Requests;

class UpdateReseauRequest extends ApiRequest
{
    public function rules(): array
    {
        return [
            'nom' => ['sometimes', 'string', 'max:100'],
            'logo' => ['sometimes', 'nullable', 'string', 'max:2048'],
        ];
    }
}
