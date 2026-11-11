<?php

namespace App\Repositories;

use PDO;
use App\DTO\Requests\GetProductsRequest;

class ProductRepository
{
    public function __construct(private PDO $pdo) {}

    public function getAll(GetProductsRequest $request): array
    {
        $conditions = [];
        $parameters = [];
        if ($request->search !== null) {
            $conditions[] = "name LIKE :search ESCAPE '!'";
            $parameters['search'] = '%' . strtr($request->search, ['!' => '!!', '%' => '!%', '_' => '!_']) . '%';
        }
        if ($request->minPrice !== null) {
            $conditions[] = 'price >= :min_price';
            $parameters['min_price'] = $request->minPrice;
        }
        if ($request->maxPrice !== null) {
            $conditions[] = 'price <= :max_price';
            $parameters['max_price'] = $request->maxPrice;
        }
        if ($request->inStock !== null) {
            $conditions[] = $request->inStock ? 'stock > 0' : 'stock = 0';
        }
        if ($request->availableOn !== null) {
            $conditions[] = '(availability_date IS NULL OR availability_date <= :available_on)';
            $parameters['available_on'] = $request->availableOn;
        }
        $where = $conditions === [] ? '' : ' WHERE ' . implode(' AND ', $conditions);
        $count = $this->pdo->prepare('SELECT COUNT(*) FROM products' . $where);
        $count->execute($parameters);
        $total = (int) $count->fetchColumn();

        $column = ['created_at' => 'created_at', 'name' => 'name', 'price' => 'price'][$request->sortBy];
        $direction = $request->sortDirection === 'asc' ? 'ASC' : 'DESC';
        $statement = $this->pdo->prepare(
            'SELECT id, name, description, price, image, availability_date, stock, created_at, updated_at FROM products'
            . $where . " ORDER BY {$column} {$direction}, id {$direction} LIMIT :limit OFFSET :offset"
        );
        foreach ($parameters as $key => $value) {
            $statement->bindValue(':' . $key, $value, PDO::PARAM_STR);
        }
        $statement->bindValue(':limit', $request->perPage, PDO::PARAM_INT);
        $statement->bindValue(':offset', ($request->page - 1) * $request->perPage, PDO::PARAM_INT);
        $statement->execute();

        return ['items' => $statement->fetchAll(PDO::FETCH_ASSOC), 'total' => $total];
    }

    public function create(array $product): bool
    {
        $sql = "
            INSERT INTO products (name, description, price, image, availability_date, stock, created_at, updated_at) 
                VALUES (:name, :description, :price, :image, :availability_date, :stock, NOW(), NOW())
        ";

        $statement = $this->pdo->prepare($sql);

        return $statement->execute([
            'name' => $product['name'],
            'description' => $product['description'],
            'price' => $product['price'],
            'image' => $product['image'],
            'availability_date' => $product['availability_date'],
            'stock' => $product['stock'],
        ]);
    }

}
