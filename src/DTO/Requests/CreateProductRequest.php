<?php

declare(strict_types=1);

namespace App\DTO\Requests;

use App\Exceptions\ValidationException;
use DateTimeImmutable;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use App\Support\ImageValidator;

final readonly class CreateProductRequest
{
    public function __construct(
        public string $name,
        public float $price,
        public ?string $description = null,
        public ?UploadedFile $image = null,
        public ?DateTimeImmutable $availabilityDate = null,
        public int $stock = 0,
        public ?string $imageExtension = null
    ) {}

    public static function fromRequest(Request $request): self
    {
        $data = $request->request->all();
        $files = $request->files->all();
        $errors = [];

        $name = $data['name'] ?? null;
        $price = $data['price'] ?? null;
        $description = $data['description'] ?? null;
        $date = $data['availability_date'] ?? null;
        $stock = $data['stock'] ?? 0;
        $image = $files['image'] ?? null;

        if (!is_string($name) || trim($name) === '') {
            $errors['name'] = 'validation.product.name';
        }

        if (!is_numeric($price) || !is_finite((float) $price)) {
            $errors['price'] = 'validation.product.price';
        }

        if ($description === '') {
            $description = null;
        }

        if ($description !== null && !is_string($description)) {
            $errors['description'] = 'validation.product.description';
        }

        $imageExtension = null;

        if ($image !== null) {
            if (!$image instanceof UploadedFile) {
                $errors['image'] = 'validation.product.image';
            } else {
                try {
                    $imageExtension = ImageValidator::validate($image);
                } catch (ValidationException $e) {
                    $errors = array_merge($errors, $e->errors());
                }
            }
        }

        $availabilityDate = null;

        if ($date !== null && $date !== '') {
            if (is_string($date) && !str_contains($date, "\0")) {
                $availabilityDate = DateTimeImmutable::createFromFormat('!Y-m-d', $date);
            }

            if (!$availabilityDate || $availabilityDate->format('Y-m-d') !== $date) {
                $errors['availability_date'] = 'validation.product.availability_date';
            }
        }

        if (!is_int($stock) || $stock < 0) {
            $errors['stock'] = 'validation.product.stock';
        }

        if ($errors !== []) {
            throw new ValidationException($errors);
        }

        return new self(
            name: trim($name),
            price: (float) $price,
            description: $description,
            image: $image,
            availabilityDate: $availabilityDate,
            stock: $stock,
            imageExtension: $imageExtension,
        );
    }
}
