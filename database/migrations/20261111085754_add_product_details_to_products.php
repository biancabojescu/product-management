<?php

use Phinx\Migration\AbstractMigration;

class AddProductDetailsToProducts extends AbstractMigration
{
    /**
     * Change Method.
     *
     * Write your reversible migrations using this method.
     *
     * More information on writing migrations is available here:
     * https://book.cakephp.org/phinx/0/en/migrations.html#the-change-method
     *
     * Remember to call "create()" or "update()" and NOT "save()" when working
     * with the Table class.
     */
    public function change(): void
    {
        $table = $this->table('products');

        $table
            ->addColumn('image', 'string', [
                'limit' => 255,
                'null' => true,
                'after' => 'price',
            ])
            ->addColumn('availability_date', 'date', [
                'null' => true,
                'after' => 'image',
            ])
            ->update();
    }
}
