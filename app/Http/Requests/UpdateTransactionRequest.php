<?php

namespace App\Http\Requests;

class UpdateTransactionRequest extends ApiRequest
{
    public function rules(): array
    {
        return [
            'montant' => ['sometimes', 'numeric', 'gt:0'],
            'solde_apres_operation' => ['sometimes', 'nullable', 'numeric', 'gte:0'],
            'note' => ['sometimes', 'nullable', 'string'],
            'motif' => ['required', 'string', 'max:1000'],
        ];
    }
}
