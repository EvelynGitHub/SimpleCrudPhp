<?php

declare(strict_types=1);

namespace SimplePhp\SimpleCrud\Facades;

use SimplePhp\SimpleCrud\Contracts\BuilderInterface;
use SimplePhp\SimpleCrud\Contracts\ExecutableInterface;
use SimplePhp\SimpleCrud\UseCases\QueryResult;

class Wrapper
{
    public function __construct(
        private BuilderInterface $builder,
        private ExecutableInterface $executor
    ) {
    }

    public function __call($method, $args)
    {
        if (method_exists($this->builder, $method)) {
            $result = $this->builder->$method(...$args);
            return $result === $this->builder ? $this : $result;
        }

        throw new \BadMethodCallException("Método $method não existe");
    }

    public function execute(): QueryResult
    {
        return $this->executor->handle($this->builder);
    }
}
