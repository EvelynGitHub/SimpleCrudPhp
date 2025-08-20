<?php

declare(strict_types=1);

namespace SimplePhp\SimpleCrud\UseCases;

class QueryResult
{
    public function __construct(
        public readonly array $fetchAll = [],
        public readonly ?array $fetch = null,
        public readonly int $rowCount = 0,
        public readonly ?string $lastInsertId = null
    ) {
    }
}
