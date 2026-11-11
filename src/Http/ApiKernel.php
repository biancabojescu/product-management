<?php

namespace App\Http;

use App\Controllers\ProductController;
use App\Middleware\BasicAuthMiddleware;
use App\Middleware\RequestValidationMiddleware;
use App\Repositories\ProductRepository;
use App\Services\ImageStorageService;
use App\Services\ProductService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class ApiKernel
{
    public function handle(Request $request): Response
    {
        try {
            $authResponse = BasicAuthMiddleware::handle();

            if ($authResponse !== null) {
                return $authResponse;
            }

            $path = rtrim(parse_url($request->getRequestUri(), PHP_URL_PATH) ?? '/', '/') ?: '/';

            return match ($path) {'/api/products' => $this->products($request),
                default => ApiResponse::error(
                    'general.not_found',
                    Response::HTTP_NOT_FOUND
                ),
            };

        } catch (Throwable $e) {
            return ApiResponse::error(
                'general.server_error',
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    private function products(Request $request): Response
    {
        return match ($request->getMethod()) {
            'POST' => $this->createProduct($request),
            default => $this->methodNotAllowed(['POST']),
        };
    }

    private function createProduct(Request $request): Response
    {
        $validationResponse = RequestValidationMiddleware::validate($request, 'multipart/form-data');

        if ($validationResponse !== null) {
            return $validationResponse;
        }

        return $this->productController()->store($request);
    }

    private function productController(): ProductController
    {
        $pdo = require dirname(__DIR__, 2) . '/config/database.php';

        return new ProductController(
            new ProductService(
                new ProductRepository($pdo),
                new ImageStorageService()
            )
        );
    }

    private function methodNotAllowed(array $methods): Response
    {
        $response = ApiResponse::error(
            'general.method_not_allowed',
            Response::HTTP_METHOD_NOT_ALLOWED
        );

        $response->headers->set('Allow', implode(', ', $methods));

        return $response;
    }
}
