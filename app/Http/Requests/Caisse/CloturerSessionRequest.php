<?php

namespace App\Http\Requests\Caisse;

use App\Http\Requests\ApiRequest;

class CloturerSessionRequest extends ApiRequest
{
    public function rules(): array
    {
        return [
            'solde_float_reel' => ['required', 'numeric', 'gte:0'],
            'solde_cash_reel' => ['required', 'numeric', 'gte:0'],
            'justification' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
