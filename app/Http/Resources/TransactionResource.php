<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;

class TransactionResource extends ApiResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'type_operation' => $this->type_operation,
            'montant' => $this->montant,
            'reference' => $this->reference,
            'solde_apres_operation' => $this->solde_apres_operation,
            'note' => $this->note,
            'statut' => $this->statut,
            'version' => $this->version,
            'created_at' => $this->created_at,
            'consentement_recap' => $this->consentement_recap,
            'consentement_methode' => $this->consentement_methode,
            'consentement_confirme_le' => $this->consentement_confirme_le,
            'client' => $this->whenLoaded('client', fn () => [
                'id' => $this->client->id,
                'telephone' => $this->client->telephone,
                'nom' => $this->client->nom,
                'prenoms' => $this->client->prenoms,
            ]),
            'reseau' => $this->whenLoaded('reseau', fn () => [
                'id' => $this->reseau->id,
                'nom' => $this->reseau->nom,
                'code' => $this->reseau->code,
            ]),
            'user' => $this->whenLoaded('user', fn () => [
                'id' => $this->user->id,
                'name' => $this->user->name,
            ]),
        ];
    }
}
