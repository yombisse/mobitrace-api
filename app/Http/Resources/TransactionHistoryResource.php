<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;

class TransactionHistoryResource extends ApiResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'action' => $this->action,
            'user' => $this->whenLoaded('user', fn () => [
                'id' => $this->user->id,
                'name' => $this->user->name,
            ]),
            'anciennes_donnees' => $this->anciennes_donnees,
            'nouvelles_donnees' => $this->nouvelles_donnees,
            'motif' => $this->motif,
            'created_at' => $this->created_at,
        ];
    }
}
