<?php

declare(strict_types=1);

namespace App\Controllers;

use App\DTO\Requests\CreateProductRequest;
use App\DTO\Requests\GetProductsRequest;
use App\Exceptions\ValidationException;
use App\Http\ApiResponse;
use App\Services\ProductService;
use App\Support\Lang;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class ProductController
{
    public function __construct(private ProductService $productService) {}

    public function getById(string $id): JsonResponse
    {
        try {
            $productId = ctype_digit($id) ? filter_var($id, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) : null;

            if (!$productId) {
                return ApiResponse::error('general.validation_error', Response::HTTP_UNPROCESSABLE_ENTITY, [
                    'id' => Lang::get('validation.product.id'),
                ]);
            }

            $product = $this->productService->getById($productId);
            if ($product === null) {
                return ApiResponse::error('product.not_found', Response::HTTP_NOT_FOUND);
            }

            return ApiResponse::success($product->toArray());
        } catch (Throwable $e) {
            error_log((string) $e);
            return ApiResponse::error('general.server_error', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function getAll(Request $request): JsonResponse
    {
        try {
            $query = GetProductsRequest::fromRequest($request);
            return ApiResponse::success($this->productService->getAll($query)->toArray());
        } catch (ValidationException $e) {
            $errors = [];
            foreach ($e->errors() as $field => $messageKey) {
                $errors[$field] = Lang::get($messageKey);
            }
            return ApiResponse::error('general.validation_error', Response::HTTP_UNPROCESSABLE_ENTITY, $errors);
        } catch (Throwable $e) {
            error_log((string) $e);
            return ApiResponse::error('general.server_error', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function store(Request $request): JsonResponse
    {
        try {
            $productRequest = CreateProductRequest::fromRequest($request);

            $created = $this->productService->create($productRequest);

            if (!$created) {
                return ApiResponse::error('general.server_error', Response::HTTP_INTERNAL_SERVER_ERROR);
            }

            return ApiResponse::success([ 'created' => true ], Response::HTTP_CREATED);
        } catch (ValidationException $e) {
            $errors = [];

            foreach ($e->errors() as $field => $messageKey) {
                $errors[$field] = Lang::get($messageKey);
            }

            return ApiResponse::error('general.validation_error', Response::HTTP_UNPROCESSABLE_ENTITY, $errors);
        } catch (Throwable $e) {
            return ApiResponse::error('general.server_error', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
