<?php

namespace App\Http\Requests\Caisse;

use App\Http\Requests\ApiRequest;
use Illuminate\Validation\Rule;

class OuvrirSessionRequest extends ApiRequest
{
    public function rules(): array
    {
        return [
            'reseau_id' => ['required', 'uuid', Rule::exists('reseaux', 'id')],
            'solde_float_ouverture' => ['required', 'numeric', 'gte:0'],
            'solde_cash_ouverture' => ['required', 'numeric', 'gte:0'],
            'justification' => ['nullable', 'string'],
        ];
    }
}
