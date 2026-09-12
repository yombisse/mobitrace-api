<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;

class UserResource extends ApiResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'telephone' => $this->telephone,
            'email' => $this->email,
            'code_agent' => $this->code_agent,
            'photo' => $this->photo,
            'localisation_point' => $this->localisation_point,
        ];
    }
}
