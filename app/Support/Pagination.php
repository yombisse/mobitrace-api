<?php

namespace App\Support;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;

final class Pagination
{
    /**
    * @return array{current_page: int, per_page: int, total: int, last_page: int}
     */
    public static function meta(LengthAwarePaginator $paginator, ?array $summary = null): array
    {
        $meta = [
            'current_page' => $paginator->currentPage(),
            'per_page' => $paginator->perPage(),
            'total' => $paginator->total(),
            'last_page' => $paginator->lastPage(),
        ];

        if ($summary !== null) {
            $meta['summary'] = $summary;
        }

        return $meta;
    }
}
