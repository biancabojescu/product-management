<?php

namespace App\Http;

use App\Support\Lang;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class ApiResponse
{
    public static function success(mixed $data = null, int $status = Response::HTTP_OK) {
        return new JsonResponse([
            'success' => true,
            'data' => $data,
        ], $status);
    }

    public static function error(
        string $messageKey,
        int $status = Response::HTTP_BAD_REQUEST,
    ) {
        return new JsonResponse([
            'success' => false,
            'error' => [
                'code' => strtoupper(
                    str_replace('.', '_', $messageKey)
                ),
                'message' => Lang::get($messageKey),
            ],
        ], $status);
    }
}
