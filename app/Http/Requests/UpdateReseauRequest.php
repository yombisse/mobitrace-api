<?php

namespace App\Http\Requests;

use App\Models\Reseau;
use Illuminate\Validation\Rule;

class UpdateReseauRequest extends ApiRequest
{
    public function rules(): array
    {
        return [
            'nom' => ['sometimes', 'string', 'max:100'],
            'code' => [
                'sometimes',
                'nullable',
                'string',
                'max:50',
                Rule::unique('reseaux', 'code')
                    ->where('owner_user_id', $this->user()->getKey())
                    ->ignore($this->route('reseau') instanceof Reseau
                        ? $this->route('reseau')->getKey()
                        : $this->route('reseau')),
            ],
            'logo' => ['sometimes', 'nullable', 'string', 'max:2048'],
        ];
    }
}
