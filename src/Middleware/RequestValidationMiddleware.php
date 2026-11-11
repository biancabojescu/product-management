<?php

namespace App\Middleware;

use App\Http\ApiResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class RequestValidationMiddleware
{
    public static function validate(Request $request, string $expectedContentType): ?JsonResponse {
        $contentType = strtolower(trim(explode(';', $request->headers->get('Content-Type', ''), 2)[0]));

        if ($contentType !== $expectedContentType) {
            return ApiResponse::error('general.invalid_content_type', Response::HTTP_UNSUPPORTED_MEDIA_TYPE);
        }

        $origin = $request->headers->get('Origin');

        if (
            ($origin !== null && $origin !== $request->getSchemeAndHttpHost()) ||
            $request->headers->get('X-Requested-With') !== 'XMLHttpRequest'
        ) {
            return ApiResponse::error('general.forbidden_origin', Response::HTTP_FORBIDDEN);
        }

        return null;
    }
}
