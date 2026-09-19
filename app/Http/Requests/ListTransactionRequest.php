<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;

class ListTransactionRequest extends ApiRequest
{
    public function rules(): array
    {
        return [
            'telephone' => ['sometimes', 'string', 'max:20'],
            'search' => ['sometimes', 'string', 'max:255'],
            'reference' => ['sometimes', 'string', 'max:100'],
            'date_debut' => ['sometimes', 'date', 'before_or_equal:today'],
            'date_fin' => ['sometimes', 'date', 'after_or_equal:date_debut', 'before_or_equal:today'],
            'periode' => ['sometimes', Rule::in(['aujourdhui', '7jours', 'cemois', 'personnalisee'])],
            'reseau_id' => ['sometimes', 'uuid', 'exists:reseaux,id'],
            'reseau_code' => ['sometimes', 'string', 'max:10'],
            'type_operation' => ['sometimes', Rule::in(['depot', 'retrait'])],
            'statut' => ['sometimes', Rule::in(['ENREGISTREE', 'MODIFIEE', 'ANNULEE'])],
            'montant_min' => ['sometimes', 'numeric', 'min:0'],
            'montant_max' => ['sometimes', 'numeric', 'min:0', 'gte:montant_min'],
            'sort_by' => ['sometimes', Rule::in(['date', 'amount', 'created_at', 'montant'])],
            'sort_order' => ['sometimes', Rule::in(['asc', 'desc'])],
            'page' => ['sometimes', 'integer', 'min:1'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            // Vérifier que la période ne dépasse pas 3 mois
            if (! empty($this->input('date_debut')) && ! empty($this->input('date_fin'))) {
                $debut = \Carbon\Carbon::parse($this->input('date_debut'));
                $fin = \Carbon\Carbon::parse($this->input('date_fin'));

                if ($debut->diffInMonths($fin) > 3) {
                    $validator->errors()->add('date_fin', 'La période ne peut pas dépasser 3 mois.');
                }
            }
        });
    }

    public function messages(): array
    {
        return [
            'date_debut.before_or_equal' => 'La date de début ne peut pas être dans le futur.',
            'date_fin.before_or_equal' => 'La date de fin ne peut pas être dans le futur.',
            'date_fin.after_or_equal' => 'La date de fin doit être supérieure ou égale à la date de début.',
            'periode.in' => 'La période doit être : aujourdhui, 7jours, cemois ou personnalisee.',
            'reseau_id.exists' => 'Le réseau sélectionné n\'existe pas.',
            'type_operation.in' => 'Le type d\'opération doit être : depot ou retrait.',
            'statut.in' => 'Le statut doit être : ENREGISTREE, MODIFIEE ou ANNULEE.',
            'montant_min.min' => 'Le montant minimum doit être supérieur ou égal à 0.',
            'montant_max.min' => 'Le montant maximum doit être supérieur ou égal à 0.',
            'montant_max.gte' => 'Le montant maximum doit être supérieur ou égal au montant minimum.',
            'sort_by.in' => 'Le champ de tri doit être : date, amount, created_at ou montant.',
            'sort_order.in' => 'L\'ordre de tri doit être : asc ou desc.',
            'per_page.min' => 'Le nombre d\'éléments par page doit être au moins 1.',
            'per_page.max' => 'Le nombre d\'éléments par page ne peut pas dépasser 100.',
        ];
    }
}
