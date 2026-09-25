<?php

namespace App\Http\Requests;

class UpdateReseauRequest extends ApiRequest
{
    public function rules(): array
    {
        return [
            'nom' => ['sometimes', 'string', 'max:100'],
            'code' => ['sometimes', 'nullable', 'string', 'max:50', 'unique:reseaux,code,'.$this->route('reseau')],
            'logo' => ['sometimes', 'nullable', 'string', 'max:2048'],
        ];
    }
}
