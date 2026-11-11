<?php

declare(strict_types=1);

namespace App\DTO\Requests;

use App\Exceptions\ValidationException;
use DateTimeImmutable;
use Symfony\Component\HttpFoundation\Request;

final readonly class GetProductsRequest
{
    private function __construct(
        public int $page,
        public int $perPage,
        public ?string $search,
        public ?string $minPrice,
        public ?string $maxPrice,
        public ?bool $inStock,
        public ?string $availableOn,
        public string $sortBy,
        public string $sortDirection,
    ) {}

    public static function fromRequest(Request $request): self
    {
        $data = $request->query->all();
        $errors = [];
        $integer = static function (string $key, int $default, int $max) use ($data, &$errors): int {
            $value = $data[$key] ?? (string) $default;
            $parsed = is_string($value) && ctype_digit($value)
                ? filter_var($value, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => $max]])
                : false;
            if ($parsed === false || $parsed > $max) {
                $errors[$key] = 'validation.products.' . $key;
                return $default;
            }
            return $parsed;
        };
        $perPage = $integer('per_page', 20, 100);
        $page = $integer('page', 1, intdiv(PHP_INT_MAX, $perPage));

        $search = $data['search'] ?? null;
        if ($search !== null) {
            if (!is_string($search) || !mb_check_encoding($search, 'UTF-8') || mb_strlen($search, 'UTF-8') > 255) {
                $errors['search'] = 'validation.products.search';
            } else {
                $search = trim($search);
                $search = $search === '' ? null : $search;
            }
        }

        $prices = [];
        foreach (['min_price', 'max_price'] as $key) {
            $value = $data[$key] ?? null;
            if ($value !== null && !self::isValidPrice($value)) {
                $errors[$key] = 'validation.products.price';
            }
            $prices[$key] = $value;
        }
        if (!isset($errors['min_price']) && !isset($errors['max_price'])
            && $prices['min_price'] !== null && $prices['max_price'] !== null
            && (float) $prices['min_price'] > (float) $prices['max_price']) {
            $errors['max_price'] = 'validation.products.price_range';
        }

        $inStock = $data['in_stock'] ?? null;
        if ($inStock !== null) {
            if (!is_string($inStock) || !in_array($inStock, ['true', 'false'], true)) {
                $errors['in_stock'] = 'validation.products.in_stock';
            } else {
                $inStock = $inStock === 'true';
            }
        }

        $availableOn = $data['available_on'] ?? null;
        if ($availableOn !== null) {
            $date = is_string($availableOn) && !str_contains($availableOn, "\0")
                ? DateTimeImmutable::createFromFormat('!Y-m-d', $availableOn) : false;
            if (!$date || $date->format('Y-m-d') !== $availableOn || $availableOn < '1000-01-01') {
                $errors['available_on'] = 'validation.products.available_on';
            }
        }

        $sortBy = $data['sort_by'] ?? 'created_at';
        $sortDirection = $data['sort_direction'] ?? 'desc';
        if (!in_array($sortBy, ['created_at', 'name', 'price'], true)) {
            $errors['sort_by'] = 'validation.products.sort_by';
        }
        if (!in_array($sortDirection, ['asc', 'desc'], true)) {
            $errors['sort_direction'] = 'validation.products.sort_direction';
        }
        if ($errors !== []) {
            throw new ValidationException($errors);
        }

        return new self($page, $perPage, $search, $prices['min_price'], $prices['max_price'], $inStock, $availableOn, $sortBy, $sortDirection);
    }

    private static function isValidPrice(mixed $value): bool
    {
        if (!is_string($value)) {
            return false;
        }

        $parts = explode('.', $value);
        if (count($parts) > 2) {
            return false;
        }

        $whole = $parts[0];
        if (!ctype_digit($whole) || strlen($whole) > 8 || (strlen($whole) > 1 && $whole[0] === '0')) {
            return false;
        }

        return !isset($parts[1]) || (ctype_digit($parts[1]) && strlen($parts[1]) <= 2);
    }
}
