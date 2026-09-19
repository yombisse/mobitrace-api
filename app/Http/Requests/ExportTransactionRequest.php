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
            'debut' => ['required', 'date', 'before_or_equal:today'],
            'fin' => ['required', 'date', 'after_or_equal:debut', 'before_or_equal:today'],
            'reseau' => ['sometimes', 'nullable', 'string'],
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $debut = \Carbon\Carbon::parse($this->input('debut'));
            $fin = \Carbon\Carbon::parse($this->input('fin'));

            // Vérifier que la période ne dépasse pas 3 mois
            if ($debut->diffInMonths($fin) > 3) {
                $validator->errors()->add('fin', 'La période ne peut pas dépasser 3 mois.');
            }
        });
    }
}
