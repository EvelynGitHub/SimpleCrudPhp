<?php

declare(strict_types=1);

namespace SimplePhp\SimpleCrud\UseCases;

use PDO;
use SimplePhp\SimpleCrud\Contracts\BuilderInterface;
use SimplePhp\SimpleCrud\Contracts\ExecutableInterface;
use SimplePhp\SimpleCrud\Core\SelectBuilder;

class ExecuteQuery implements ExecutableInterface
{
    public function __construct(private PDO $pdo)
    {
    }

    public function handle(BuilderInterface $builder): array|int
    {
        $stmt = $this->pdo->prepare($builder->getSql());
        // $type = $stmt->queryString;

        $stmt->execute($builder->getBindings());

        return match (true) {
            $builder instanceof SelectBuilder => $stmt->fetchAll(\PDO::FETCH_ASSOC),
            default => $stmt->rowCount()
        };
    }
}
