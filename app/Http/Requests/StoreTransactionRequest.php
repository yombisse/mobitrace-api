<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;

class StoreTransactionRequest extends ApiRequest
{
    public function rules(): array
    {
        return [
            'telephone' => ['required', 'string', 'max:20'],
            'nom' => ['sometimes', 'nullable', 'string', 'max:100'],
            'prenoms' => ['sometimes', 'nullable', 'string', 'max:150'],
            'date_naissance' => ['sometimes', 'nullable', 'date'],
            'nationalite' => ['sometimes', 'nullable', 'string', 'max:100'],
            'type_piece' => ['sometimes', 'nullable', 'string', 'max:50'],
            'numero_piece' => ['sometimes', 'nullable', 'string', 'max:50'],
            'date_expiration_piece' => ['sometimes', 'nullable', 'date'],
            'reseau_id' => [
                'required',
                'uuid',
                Rule::exists('reseaux', 'id')->whereNull('deleted_at'),
            ],
            'type_operation' => ['required', Rule::in(['depot', 'retrait'])],
            'montant' => ['required', 'numeric', 'gt:0'],
            'solde_apres_operation' => ['sometimes', 'nullable', 'numeric', 'gte:0'],
            'note' => ['sometimes', 'nullable', 'string'],
            'consentement_recap' => ['sometimes', 'nullable', 'string'],
            'consentement_methode' => ['sometimes', 'string', 'in:confirmation_client'],
        ];
    }
}
