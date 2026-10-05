<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateZoosTable extends AbstractMigration
{
    public function change(): void
    {
        $table = $this->table('zoos');
        $table
            ->addColumn('name', 'string', ['limit' => 255])
            ->addColumn('city', 'string', ['limit' => 255])
            ->addTimestamps()
            ->create();
    }
}