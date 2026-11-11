<?php

namespace App\Services;

use App\DTO\Requests\CreateProductRequest;
use App\DTO\Requests\GetProductsRequest;
use App\DTO\Responses\ProductResponse;
use App\DTO\Responses\PaginatedResult;
use App\Repositories\ProductRepository;
use RuntimeException;
use Throwable;

class ProductService
{
    public function __construct(
        private ProductRepository $productRepository,
        private ImageStorageService $imageStorageService
    ) {}

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
                'price' => number_format($request->price, 2, '.', ''),
                'image' => $imagePath,
                'availability_date' =>
                    $request->availabilityDate?->format('Y-m-d'),
                'stock' => $request->stock,
            ]);

            if (!$created) throw new RuntimeException('Product could not be created.');

            return true;
        } catch (Throwable $e) {
            if ($imagePath !== null) {
                try {
                    $this->imageStorageService->delete($imagePath);
                } catch (Throwable $cleanupError) {
                    error_log((string) $cleanupError);
                }
            }

            throw $e;
        }
    }
}
