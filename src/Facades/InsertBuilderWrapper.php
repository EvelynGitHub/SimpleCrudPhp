<?php

declare(strict_types=1);

namespace SimplePhp\SimpleCrud\Facades;

use SimplePhp\SimpleCrud\Core\InsertBuilder;
use SimplePhp\SimpleCrud\UseCases\ExecuteInsert;


class InsertBuilderWrapper
{
    public function __construct(
        private InsertBuilder $builder,
        private ExecuteInsert $executor
    ) {
    }

    public function values(array $data): static
    {
        $this->builder->values($data);
        return $this;
    }

    public function execute(): bool
    {
        return $this->executor->handle($this->builder);
    }
}
