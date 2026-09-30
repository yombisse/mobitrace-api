<?php

namespace App\Http\Requests\Caisse;

use App\Http\Requests\ApiRequest;

class ToggleReseauRequest extends ApiRequest
{
    public function rules(): array
    {
        return [
            'actif' => ['required', 'boolean'],
        ];
    }
}
