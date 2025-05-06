<?php

declare(strict_types=1);

namespace SimplePhp\SimpleCrud\Core\UseCases;

use SimplePhp\SimpleCrud\Infra\Database\Connection;

class MigrationRunner
{
    public static function run(string $migrationPath): void
    {
        $pdo = Connection::getInstance();
        $files = glob($migrationPath . '/*.sql');

        foreach ($files as $file) {
            $sql = file_get_contents($file);
            $pdo->exec($sql);
            echo "Migration executada: " . basename($file) . PHP_EOL;
        }
    }
}
