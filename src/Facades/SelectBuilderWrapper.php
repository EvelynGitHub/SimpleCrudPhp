<?php
declare(strict_types=1);

namespace SimplePhp\SimpleCrud\Facades;

use SimplePhp\SimpleCrud\Core\SelectBuilder;
use SimplePhp\SimpleCrud\UseCases\ExecuteSelect;

class SelectBuilderWrapper
{
    public function __construct(
        private SelectBuilder $builder,
        private ExecuteSelect $executor
    ) {
    }

    // public function select(array $columns): static
    // {
    //     $this->builder->select($columns);
    //     return $this;
    // }

    // public function where(string $column, string $operator, mixed $value): static
    // {
    //     $this->builder->where($column);
    //     return $this;
    // }

    // public function limit(int $limit): static
    // {
    //     $this->builder->limit($limit);
    //     return $this;
    // }

    public function __call($method, $args)
    {
        if (method_exists($this->builder, $method)) {
            $result = $this->builder->$method(...$args);
            return $result === $this->builder ? $this : $result;
        }

        throw new \BadMethodCallException("Método $method não existe");
    }

    public function execute(): array
    {
        return $this->executor->handle($this->builder);
    }
}