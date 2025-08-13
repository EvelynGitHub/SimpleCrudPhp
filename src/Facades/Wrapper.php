<?php

declare(strict_types=1);

namespace SimplePhp\SimpleCrud\Facades;

use SimplePhp\SimpleCrud\Contracts\BuilderInterface;
use SimplePhp\SimpleCrud\Contracts\ExecutableInterface;
use SimplePhp\SimpleCrud\UseCases\QueryResult;

/**
 * @method Wrapper from(string $table, ?string $alias = null)
 * @method Wrapper where(string $column, string $operator, mixed $value)
 * @method Wrapper andWhere(string $column, string $operator, mixed $value)
 * @method Wrapper orWhere(string $column, string $operator, mixed $value)
 * @method Wrapper orderBy(string $column, string $direction = 'ASC')
 * @method Wrapper limit(int $limit)
 * @method Wrapper offset(int $offset)
 * @method Wrapper join(string $table, string $first, string $operator, string $second)
 * @method Wrapper leftJoin(string $table, string $first, string $operator, string $second)
 * @method Wrapper rightJoin(string $table, string $first, string $operator, string $second)
 * @method Wrapper distinct()
 * @method Wrapper groupBy(string ...$columns)
 * @method Wrapper having(string $column, string $operator, mixed $value)
 * @method Wrapper insert(array $data)
 * @method Wrapper update(array $data)
 * @method Wrapper delete()
 * @method Wrapper values(array $rows)
 * @method Wrapper set(string|array $column, mixed $value = null)
 * @mixin BuilderInterface
 */
class Wrapper
{
    public function __construct(
        private BuilderInterface $builder,
        private ExecutableInterface $executor
    ) {
    }

    /**
     * Método mágico para chamar métodos do construtor de consultas SQL.
     * @param mixed $method método do construtor de consulta SQL (BuilderInterface)
     * @param mixed $args
     * @return mixed
     * @throws \BadMethodCallException
     */
    public function __call($method, $args): mixed
    {
        if (method_exists($this->builder, $method)) {
            $result = $this->builder->$method(...$args);
            return $result === $this->builder ? $this : $result;
        }

        throw new \BadMethodCallException("Método $method não existe");
    }

    /**
     * Executa a consulta SQL construída e retorna o resultado.
     * @return QueryResult
     */
    public function all(): QueryResult
    {
        return $this->executor->handle($this->builder);
    }

    /**
     * Executa a consulta SQL construída utilizando bindvalue() e retorna o resultado.
     * @return QueryResult
     */
    public function execute(): QueryResult
    {
        return $this->executor->execute($this->builder);
    }

    public function fetch(): QueryResult
    {
        return $this->executor->execute($this->builder);
    }

    public function lastId(): int|null
    {
        return $this->executor->lastId($this->builder);
    }
}
