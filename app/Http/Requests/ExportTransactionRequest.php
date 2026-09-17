<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ExportTransactionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'debut' => ['required', 'date'],
            'fin' => ['required', 'date', 'after_or_equal:debut'],
            'reseau' => ['sometimes', 'nullable', 'string'],
        ];
    }
}
