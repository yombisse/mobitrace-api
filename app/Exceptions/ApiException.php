<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ApiException extends Exception
{
    /**
     * @param  array<string, array<int, string>>  $errors
     * @param  array<string, mixed>  $meta
     */
    public function __construct(
        string $message,
        private readonly int $status = 422,
        private readonly array $errors = [],
        private readonly array $meta = [],
    ) {
        parent::__construct($message);
    }

    public function render(Request $request): JsonResponse
    {
        $response = [
            'success' => false,
            'message' => $this->getMessage(),
        ];

        if ($this->errors !== []) {
            $response['errors'] = $this->errors;
        }

        if ($this->meta !== []) {
            $response['meta'] = $this->meta;
        }

        return response()->json($response, $this->status);
    }
}
