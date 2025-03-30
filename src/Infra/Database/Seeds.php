<?php

declare(strict_types=1);

namespace SimplePhp\SimpleCrud\Infra\Database;

use SimplePhp\SimpleCrud\Core\UseCases\SeedRunner;

class Seeds
{

    // private string $dirSeedPath;

    // public function __construct(string $dirSeedPath)
    // {
    //     $this->dirSeedPath = $dirSeedPath;
    // }

    // public static function run(): void
    // {
    //     // $pdo = Connection::getInstance();
    //     // SeedRunner::run($this->dirSeedPath, $pdo);

    //     $runner = new SeedRunner(new Connection());
    //     $runner->run(__DIR__ . '/../../../seeds');

    // }

    public static function run(string $path): void
    {
        $pdo = Connection::getInstance();
        SeedRunner::run($path, $pdo);
    }
}