<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ApiException extends Exception
{
    /**
     * @param  array<string, array<int, string>>  $errors
     */
    public function __construct(
        string $message,
        private readonly int $status = 422,
        private readonly array $errors = [],
    ) {
        parent::__construct($message);
    }

    public function render(Request $request): JsonResponse
    {
        $response = ['message' => $this->getMessage()];

        if ($this->errors !== []) {
            $response['errors'] = $this->errors;
        }

        return response()->json($response, $this->status);
    }
}
