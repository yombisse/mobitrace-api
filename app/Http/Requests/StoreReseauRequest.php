<?php

namespace App\Http\Requests;

class StoreReseauRequest extends ApiRequest
{
    public function rules(): array
    {
        return [
            'nom' => ['required', 'string', 'max:100'],
            'code' => ['required', 'string', 'max:50', 'unique:reseaux,code'],
            'logo' => ['sometimes', 'nullable', 'string', 'max:2048'],
        ];
    }
}
