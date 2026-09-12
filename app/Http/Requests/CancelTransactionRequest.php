<?php

namespace App\Http\Requests;

class CancelTransactionRequest extends ApiRequest
{
    public function rules(): array
    {
        return [
            'motif' => ['required', 'string', 'max:1000'],
        ];
    }
}
