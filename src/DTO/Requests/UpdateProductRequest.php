<?php

declare(strict_types=1);

namespace App\DTO\Requests;

use App\Exceptions\ValidationException;
use App\Support\ImageValidator;
use App\Support\ProductDate;
use DateTimeImmutable;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;

final readonly class UpdateProductRequest
{
    private function __construct(
        public array $changes,
        public ?UploadedFile $image,
        public ?string $imageExtension,
    ) {}

    public static function fromRequest(Request $request): self
    {
        $data = $request->request->all();
        $files = $request->files->all();
        $errors = [];
        $changes = [];
        $allowed = ['name', 'description', 'price', 'stock', 'availability_date', 'image'];
        foreach (array_keys($data + $files) as $field) {
            if (!in_array($field, $allowed, true)) {
                $errors[$field] = 'validation.product.update_field';
            }
        }
        if (array_key_exists('name', $data)) {
            $name = $data['name'];
            if (!is_string($name) || !mb_check_encoding($name, 'UTF-8') || trim($name) === '' || mb_strlen(trim($name), 'UTF-8') > 255) {
                $errors['name'] = 'validation.product.name';
            } else {
                $changes['name'] = trim($name);
            }
        }
        if (array_key_exists('description', $data)) {
            $description = $data['description'];
            if ($description !== null && (!is_string($description) || !mb_check_encoding($description, 'UTF-8') || strlen($description) > 65535)) {
                $errors['description'] = 'validation.product.description';
            } else {
                $changes['description'] = $description === '' ? null : $description;
            }
        }
        if (array_key_exists('price', $data)) {
            $price = $data['price'];
            $parts = is_string($price) || is_int($price) || is_float($price) ? explode('.', (string) $price) : [];
            $whole = $parts[0] ?? '';
            if (count($parts) > 2 || !ctype_digit($whole) || strlen($whole) > 8
                || (strlen($whole) > 1 && $whole[0] === '0')
                || (isset($parts[1]) && (!ctype_digit($parts[1]) || strlen($parts[1]) > 2))) {
                $errors['price'] = 'validation.products.price';
            } else {
                $changes['price'] = $whole . '.' . str_pad($parts[1] ?? '', 2, '0');
            }
        }
        if (array_key_exists('stock', $data)) {
            $value = $data['stock'];
            $stock = (is_int($value) || (is_string($value) && ctype_digit($value)))
                ? filter_var($value, FILTER_VALIDATE_INT, ['options' => ['min_range' => 0, 'max_range' => 4294967295]]) : false;
            if ($stock === false) {
                $errors['stock'] = 'validation.product.stock';
            } else {
                $changes['stock'] = $stock;
            }
        }
        if (array_key_exists('availability_date', $data)) {
            $value = $data['availability_date'];
            if ($value === null || $value === '') {
                $changes['availability_date'] = ProductDate::today()->format('Y-m-d');
            } else {
                $date = is_string($value) && !str_contains($value, "\0")
                    ? DateTimeImmutable::createFromFormat('!Y-m-d', $value) : false;
                if (!$date || $date->format('Y-m-d') !== $value || $value < '1000-01-01') {
                    $errors['availability_date'] = 'validation.product.availability_date';
                } else {
                    $changes['availability_date'] = $value;
                }
            }
        }
        $image = $files['image'] ?? null;
        $extension = null;
        if (array_key_exists('image', $data)) {
            if ($data['image'] !== null || $image !== null) {
                $errors['image'] = 'validation.product.image';
            } else {
                $changes['image'] = null;
            }
        }
        if ($image !== null) {
            if (!$image instanceof UploadedFile) {
                $errors['image'] = 'validation.product.image';
            } else {
                try {
                    $extension = ImageValidator::validate($image);
                } catch (ValidationException $e) {
                    $errors = array_merge($errors, $e->errors());
                }
            }
        }
        foreach ($files as $field => $file) {
            if ($field !== 'image') {
                $errors[$field] = 'validation.product.update_field';
            }
        }
        if ($changes === [] && $image === null && $errors === []) {
            $errors['fields'] = 'validation.product.update_empty';
        }
        if ($errors !== []) {
            throw new ValidationException($errors);
        }
        return new self($changes, $image, $extension);
    }
}
