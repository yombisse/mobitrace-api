<?php

namespace App\Http\Requests\Caisse;

use App\Http\Requests\ApiRequest;
use Illuminate\Validation\Rule;

class AjustementCaisseRequest extends ApiRequest
{
    public function rules(): array
    {
        return [
            'reseau_id' => ['required', 'uuid', Rule::exists('reseaux', 'id')],
            'type' => ['required', 'in:recharge,correction'],
            'montant_float' => ['required', 'numeric'],
            'montant_cash' => ['required', 'numeric'],
            'motif' => ['required', 'string', 'max:1000'],
        ];
    }
}
