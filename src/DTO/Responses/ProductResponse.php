<?php

declare(strict_types=1);

namespace App\DTO\Responses;

final readonly class ProductResponse
{
    public function __construct(
        public int $id,
        public string $name,
        public ?string $description,
        public string $price,
        public ?string $image,
        public ?string $availabilityDate,
        public int $stock,
        public ?string $createdAt,
        public ?string $updatedAt,
    ) {}

    public static function fromArray(array $product): self
    {
        // DECIMAL is fetched as a string by PDO; pad without converting to float.
        $parts = explode('.', (string) $product['price'], 2);
        $price = $parts[0] . '.' . str_pad($parts[1] ?? '', 2, '0');

        return new self(
            (int) $product['id'], $product['name'], $product['description'],
            $price, $product['image'], $product['availability_date'],
            (int) $product['stock'], $product['created_at'], $product['updated_at'],
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'price' => $this->price,
            'image' => $this->image,
            'availability_date' => $this->availabilityDate,
            'stock' => $this->stock,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
        ];
    }
}
