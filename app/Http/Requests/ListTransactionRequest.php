<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;

class ListTransactionRequest extends ApiRequest
{
    public function rules(): array
    {
        return [
            'telephone' => ['sometimes', 'string', 'max:20'],
            'search' => ['sometimes', 'string', 'max:255'],
            'reference' => ['sometimes', 'string', 'max:100'],
            'date_debut' => ['sometimes', 'date'],
            'date_fin' => ['sometimes', 'date', 'after_or_equal:date_debut'],
            'reseau_id' => ['sometimes', 'uuid', 'exists:reseaux,id'],
            'type_operation' => ['sometimes', Rule::in(['depot', 'retrait'])],
            'statut' => ['sometimes', Rule::in(['ENREGISTREE', 'MODIFIEE', 'ANNULEE'])],
            'page' => ['sometimes', 'integer', 'min:1'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
        ];
    }
}
