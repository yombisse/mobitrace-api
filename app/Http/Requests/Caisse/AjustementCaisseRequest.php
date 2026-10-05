<?php

namespace App\Http\Requests\Caisse;

use App\Http\Requests\ApiRequest;
use Illuminate\Validation\Rule;

class AjustementCaisseRequest extends ApiRequest
{
    public function rules(): array
    {
        return [
            'reseau_id' => ['required', 'uuid', Rule::exists('reseaux', 'id')],
            'type' => ['required', 'in:recharge,correction'],
            'montant_float' => ['nullable', 'numeric', 'different:0', 'max:999999999999999.99', 'min:-999999999999999.99'],
            'montant_cash' => ['nullable', 'numeric', 'different:0', 'max:999999999999999.99', 'min:-999999999999999.99'],
            'motif' => ['required', 'string', 'max:1000'],
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $montantFloat = $this->input('montant_float');
            $montantCash = $this->input('montant_cash');

            // Au moins un des deux montants doit être non-null et différent de 0
            if (($montantFloat === null || $montantFloat == 0) && ($montantCash === null || $montantCash == 0)) {
                $validator->errors()->add('montant', 'Au moins un des montants (float ou cash) doit être renseigné et différent de 0.');
            }
        });
    }
}
