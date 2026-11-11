<?php

declare(strict_types=1);

namespace App\DTO\Responses;

final readonly class PaginatedResult
{
    /** @param list<ProductResponse> $items */
    public function __construct(
        public array $items,
        public int $total,
        public int $currentPage,
        public int $perPage,
    ) {}

    public function toArray(): array
    {
        $lastPage = max(1, intdiv($this->total, $this->perPage) + ($this->total % $this->perPage !== 0 ? 1 : 0));

        return [
            'items' => array_map(static fn (ProductResponse $product): array => $product->toArray(), $this->items),
            'pagination' => [
                'current_page' => $this->currentPage,
                'per_page' => $this->perPage,
                'total' => $this->total,
                'last_page' => $lastPage,
                'has_next_page' => $this->currentPage < $lastPage,
                'has_previous_page' => $this->currentPage > 1,
            ],
        ];
    }
}
