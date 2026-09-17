<?php

namespace App\Http\Requests;

class ConfirmConsentRequest extends ApiRequest
{
    public function rules(): array
    {
        return [
            'consentement_recap' => ['sometimes', 'nullable', 'string'],
        ];
    }
}
