<?php

declare(strict_types=1);

namespace SimplePhp\SimpleCrud\UseCases;

use PDO;
use SimplePhp\SimpleCrud\Contracts\BuilderInterface;
use SimplePhp\SimpleCrud\Contracts\ExecutableInterface;

class ExecuteQuery implements ExecutableInterface
{
    public function __construct(private PDO $pdo)
    {
    }

    public function handle(BuilderInterface $builder): QueryResult
    {
        $stmt = $this->pdo->prepare($builder->getSql());
        $stmt->execute($builder->getBindings());

        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return new QueryResult(
            fetchAll: $results,
            fetch: $results[0] ?? null,
            rowCount: $stmt->rowCount(),
            lastInsertId: $this->pdo->lastInsertId() ?: null
        );
    }
}
