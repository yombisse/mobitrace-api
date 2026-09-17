<?php

namespace App\Support;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\JsonResponse;

final class ApiResponse
{
    public static function success(
        mixed $data = null,
        string $message = 'Opération effectuée avec succès.',
        int $status = 200,
        array $meta = [],
    ): JsonResponse {
        $response = [
            'success' => true,
            'message' => $message,
        ];

        if ($data !== null) {
            $response['data'] = $data;
        }

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
