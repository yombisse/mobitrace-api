<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;

class DashboardResource extends ApiResource
{
    public function toArray(Request $request): array
    {
        return [
            'date' => $this->resource['date'],
            'nombre_transactions' => $this->resource['nombre_transactions'],
            'total_depots' => $this->resource['total_depots'],
            'total_retraits' => $this->resource['total_retraits'],
            'dernieres_transactions' => TransactionResource::collection(
                $this->resource['dernieres_transactions'],
            )->resolve($request),
            'derniers_soldes' => $this->resource['derniers_soldes'],
        ];
    }
}
