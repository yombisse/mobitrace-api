<?php

namespace App\Http\Requests;

class UpdateClientRequest extends ApiRequest
{
    public function rules(): array
    {
        return [
            'nom' => ['sometimes', 'nullable', 'string', 'max:100'],
            'prenoms' => ['sometimes', 'nullable', 'string', 'max:150'],
            'date_naissance' => ['sometimes', 'nullable', 'date'],
            'nationalite' => ['sometimes', 'nullable', 'string', 'max:100'],
            'type_piece' => ['sometimes', 'nullable', 'string', 'max:50'],
            'numero_piece' => ['sometimes', 'nullable', 'string', 'max:50'],
            'date_expiration_piece' => ['sometimes', 'nullable', 'date'],
        ];
    }
}
