<?php

namespace App\Http\Requests;

class ScanCnibRequest extends ApiRequest
{
    public function rules(): array
    {
        return [
            'recto' => [
                'required',
                'file',
                'mimes:jpeg,jpg,png,webp',
                'max:10240',
            ],
            'verso' => [
                'sometimes',
                'nullable',
                'file',
                'mimes:jpeg,jpg,png,webp',
                'max:10240',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'recto.required' => 'L\'image recto de la pièce d\'identité est obligatoire.',
            'recto.mimes' => 'L\'image recto doit être au format JPEG, JPG, PNG ou WEBP.',
            'recto.max' => 'L\'image recto ne doit pas dépasser 10 Mo.',
            'verso.mimes' => 'L\'image verso doit être au format JPEG, JPG, PNG ou WEBP.',
            'verso.max' => 'L\'image verso ne doit pas dépasser 10 Mo.',
        ];
    }
}
