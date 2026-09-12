<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;

class ClientResource extends ApiResource
{
    public function toArray(Request $request): array
    {
        $data = [
            'id' => $this->id,
            'telephone' => $this->telephone,
            'nom' => $this->nom,
            'prenoms' => $this->prenoms,
        ];

        if (array_key_exists('transactions_count', $this->resource->getAttributes())) {
            $data['nombre_transactions'] = (int) $this->transactions_count;
            $data['derniere_transaction_at'] = $this->transactions_max_created_at;
        }

        if (array_key_exists('statistiques', $this->resource->getAttributes())) {
            $data['date_naissance'] = $this->date_naissance?->toDateString();
            $data['nationalite'] = $this->nationalite;
            $data['type_piece'] = $this->type_piece;
            $data['numero_piece'] = $this->numero_piece;
            $data['date_expiration_piece'] = $this->date_expiration_piece?->toDateString();
            $data['created_at'] = $this->created_at;
            $data['statistiques'] = $this->statistiques;
        }

        return $data;
    }
}
