<?php

namespace App\Repositories;

use PDO;

class ProductRepository
{
    public function __construct(
        private PDO $pdo
    ) {}

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