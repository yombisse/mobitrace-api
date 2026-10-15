<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;

class StoreReseauRequest extends ApiRequest
{
    public function rules(): array
    {
        return [
            'nom' => ['required', 'string', 'max:100'],
            'code' => [
                'sometimes',
                'nullable',
                'string',
                'max:50',
                Rule::unique('reseaux', 'code')->where('owner_user_id', $this->user()->getKey()),
            ],
            'logo' => ['sometimes', 'nullable', 'string', 'max:2048'],
        ];
    }
}
