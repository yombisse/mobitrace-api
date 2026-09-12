<?php

namespace App\Support;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;

final class Pagination
{
    /**
     * @return array{current_page: int, per_page: int, total: int}
     */
    public static function meta(LengthAwarePaginator $paginator): array
    {
        return [
            'current_page' => $paginator->currentPage(),
            'per_page' => $paginator->perPage(),
            'total' => $paginator->total(),
        ];
    }
}
