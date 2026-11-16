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

            $segments = explode('/', trim($path, '/'));
            if (count($segments) === 3 && $segments[0] === 'api' && $segments[1] === 'products') {
                return match ($request->getMethod()) {
                    'GET' => $this->productController()->getById($segments[2]),
                    'DELETE' => $this->productController()->delete($segments[2]),
                    'PATCH', 'POST' => $this->updateProduct($request, $segments[2]),
                    default => $this->methodNotAllowed(['GET', 'PATCH', 'POST', 'DELETE']),
                };
            }

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
            'GET' => $this->productController()->getAll($request),
            'POST' => $this->createProduct($request),
            default => $this->methodNotAllowed(['GET', 'POST']),
        };
    }

    private function updateProduct(Request $request, string $id): Response
    {
        $contentType = $request->getMethod() === 'PATCH' ? 'application/json' : 'multipart/form-data';
        $error = RequestValidationMiddleware::validate($request, $contentType);
        if ($error !== null) {
            return $error;
        }
        if ($contentType === 'application/json') {
            try {
                $data = json_decode($request->getContent(), false, 512, JSON_THROW_ON_ERROR);
            } catch (\JsonException $e) {
                return ApiResponse::error('general.invalid_json', Response::HTTP_BAD_REQUEST);
            }
            if (!$data instanceof \stdClass) {
                return ApiResponse::error('general.invalid_json', Response::HTTP_BAD_REQUEST);
            }
            $request->request->replace((array) $data);
        }
        return $this->productController()->update($request, $id);
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
