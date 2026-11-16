<?php

declare(strict_types=1);

namespace App\DTO\Requests;

use App\Exceptions\ValidationException;
use DateTimeImmutable;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use App\Support\ImageValidator;
use App\Support\ProductDate;

final readonly class CreateProductRequest
{
    private function __construct(
        public string $name,
        public string $price,
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

        $allowed = ['name', 'description', 'price', 'stock', 'availability_date', 'image'];
        foreach (array_keys($data + $files) as $field) {
            if (!in_array($field, $allowed, true)) {
                $errors[$field] = 'validation.product.create_field';
            }
        }

        $name = $data['name'] ?? null;
        $price = $data['price'] ?? null;
        $description = $data['description'] ?? null;
        $date = $data['availability_date'] ?? null;
        $stock = array_key_exists('stock', $data) ? $data['stock'] : 0;
        $image = $files['image'] ?? null;

        if (!is_string($name) || !mb_check_encoding($name, 'UTF-8') || trim($name) === '' || mb_strlen(trim($name), 'UTF-8') > 255) {
            $errors['name'] = 'validation.product.name';
        }

        $parts = is_string($price) || is_int($price) || is_float($price) ? explode('.', (string) $price) : [];
        $whole = $parts[0] ?? '';
        if (count($parts) > 2 || !ctype_digit($whole) || strlen($whole) > 8
            || (strlen($whole) > 1 && $whole[0] === '0')
            || (isset($parts[1]) && (!ctype_digit($parts[1]) || strlen($parts[1]) > 2))) {
            $errors['price'] = 'validation.products.price';
        } else {
            $price = $whole . '.' . str_pad($parts[1] ?? '', 2, '0');
        }

        if ($description === '') {
            $description = null;
        }

        if ($description !== null && (!is_string($description) || !mb_check_encoding($description, 'UTF-8') || strlen($description) > 65535)) {
            $errors['description'] = 'validation.product.description';
        }

        $imageExtension = null;

        if (array_key_exists('image', $data) && ($data['image'] !== null || $image !== null)) {
            $errors['image'] = 'validation.product.image';
        }

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

        $availabilityDate = ProductDate::today();

        if ($date !== null && $date !== '') {
            $availabilityDate = null;
            if (is_string($date) && !str_contains($date, "\0")) {
                $availabilityDate = DateTimeImmutable::createFromFormat('!Y-m-d', $date);
            }

            if (!$availabilityDate || $availabilityDate->format('Y-m-d') !== $date || $date < '1000-01-01') {
                $errors['availability_date'] = 'validation.product.availability_date';
            }
        }

        foreach ($files as $field => $file) {
            if ($field !== 'image') {
                $errors[$field] = 'validation.product.create_field';
            }
        }

        $stock = (is_int($stock) || (is_string($stock) && ctype_digit($stock)))
            ? filter_var($stock, FILTER_VALIDATE_INT, ['options' => ['min_range' => 0, 'max_range' => 4294967295]])
            : false;

        if ($stock === false) {
            $errors['stock'] = 'validation.product.stock';
        }

        if ($errors !== []) {
            throw new ValidationException($errors);
        }

        return new self(
            name: trim($name),
            price: $price,
            description: $description,
            image: $image,
            availabilityDate: $availabilityDate,
            stock: $stock,
            imageExtension: $imageExtension,
        );
    }
}
