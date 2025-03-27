<?php

declare(strict_types=1);

namespace SimplePhp\SimpleCrud\Infra\Database;

use SimplePhp\SimpleCrud\Core\UseCases\MigrationRunner;


class Migrations
{
    public static function run(string $migrationPath): void
    {
        MigrationRunner::run($migrationPath);
    }
}