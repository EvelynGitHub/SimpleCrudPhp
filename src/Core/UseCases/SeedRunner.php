<?php

declare(strict_types=1);

namespace SimplePhp\SimpleCrud\Core\UseCases;

use PDO;
use SimplePhp\SimpleCrud\Core\Interfaces\DatabaseConnectionInterface;


class SeedRunner
{
    private PDO $pdo;

    public function __construct(DatabaseConnectionInterface $connection)
    {
        $this->pdo = $connection::getInstance();
    }

    public static function run(string $seedPath, $pdo): void
    {
        $files = glob($seedPath . '/*.sql');

        foreach ($files as $file) {
            $sql = file_get_contents($file);
            $pdo->exec($sql);
            echo "Seed executado: " . basename($file) . PHP_EOL;
        }
    }
}