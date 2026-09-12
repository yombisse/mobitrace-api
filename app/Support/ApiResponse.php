<?php

namespace App\Support;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\JsonResponse;

final class ApiResponse
{
    public static function success(
        mixed $data = [],
        string $message = 'Opération effectuée avec succès.',
        int $status = 200,
        array $meta = [],
    ): JsonResponse {
        $response = [
            'data' => $data,
            'message' => $message,
        ];

        if ($meta !== []) {
            $response['meta'] = $meta;
        }

        return response()->json($response, $status);
    }

    public static function paginated(
        LengthAwarePaginator $paginator,
        string $message = 'Opération effectuée avec succès.',
    ): JsonResponse {
        return self::success(
            $paginator->items(),
            $message,
            200,
            Pagination::meta($paginator),
        );
    }
}
