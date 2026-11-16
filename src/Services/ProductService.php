<?php

namespace App\Services;

use App\DTO\Requests\CreateProductRequest;
use App\DTO\Requests\UpdateProductRequest;
use App\DTO\Requests\GetProductsRequest;
use App\DTO\Responses\ProductResponse;
use App\DTO\Responses\PaginatedResult;
use App\Repositories\ProductRepository;
use App\Support\ProductDate;
use RuntimeException;
use Throwable;

class ProductService
{
    public function __construct(
        private ProductRepository $productRepository,
        private ImageStorageService $imageStorageService
    ) {}

    public function delete(int $id): bool
    {
        $product = $this->productRepository->delete($id);
        if ($product === null) {
            return false;
        }

        $this->deleteImageSafely($product['image']);
        return true;
    }

    public function update(int $id, UpdateProductRequest $request): ?ProductResponse
    {
        $changes = $request->changes;
        $newImage = null;
        try {
            if ($request->image !== null) {
                $newImage = $this->imageStorageService->store($request->image, $request->imageExtension);
                $changes['image'] = $newImage;
            }
            $result = $this->productRepository->update($id, $changes);
        } catch (Throwable $e) {
            if ($newImage !== null) {
                $this->deleteImageSafely($newImage);
            }
            throw $e;
        }
        if ($result === null) {
            if ($newImage !== null) {
                $this->deleteImageSafely($newImage);
            }
            return null;
        }
        if (array_key_exists('image', $changes) && $result['previous_image'] !== $changes['image']) {
            $this->deleteImageSafely($result['previous_image']);
        }
        return ProductResponse::fromArray($result['product']);
    }

    private function deleteImageSafely(?string $path): void
    {
        try {
            $this->imageStorageService->delete($path);
        } catch (Throwable $e) {
            error_log((string) $e);
        }
    }

    public function getAll(GetProductsRequest $request): PaginatedResult
    {
        $result = $this->productRepository->getAll($request);

        return new PaginatedResult(
            array_map(static fn (array $product): ProductResponse => ProductResponse::fromArray($product), $result['items']),
            $result['total'],
            $request->page,
            $request->perPage,
        );
    }

    public function getById(int $id): ?ProductResponse
    {
        $product = $this->productRepository->getById($id);

        return $product === null ? null : ProductResponse::fromArray($product);
    }

    public function create(CreateProductRequest $request): bool
    {
        $imagePath = null;

        try {
            if ($request->image !== null) {
                $imagePath = $this->imageStorageService->store($request->image, $request->imageExtension);
            }

            $created = $this->productRepository->create([
                'name' => $request->name,
                'description' => $request->description,
                'price' => $request->price,
                'image' => $imagePath,
                'availability_date' =>
                    ($request->availabilityDate ?? ProductDate::today())->format('Y-m-d'),
                'stock' => $request->stock,
            ]);

            if (!$created) throw new RuntimeException('Product could not be created.');

            return true;
        } catch (Throwable $e) {
            if ($imagePath !== null) {
                try {
                    $this->imageStorageService->delete($imagePath);
                } catch (Throwable $e) {
                    error_log((string) $e);
                }
            }

            throw $e;
        }
    }
}
