<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;

class ReseauResource extends ApiResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'nom' => $this->nom,
            'code' => $this->code,
            'logo' => $this->logo,
            'deleted_at' => $this->deleted_at,
        ];
    }
}
