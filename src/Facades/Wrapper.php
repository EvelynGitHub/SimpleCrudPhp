<?php

declare(strict_types=1);

namespace SimplePhp\SimpleCrud\Facades;

use SimplePhp\SimpleCrud\Contracts\BuilderInterface;
use SimplePhp\SimpleCrud\Contracts\ExecutableInterface;
use SimplePhp\SimpleCrud\UseCases\QueryResult;

/**
 * @method Wrapper from(string $table)
 * @method Wrapper join(string $table, string $onCondition, string $type = 'INNER')
 * @method Wrapper where($column, $operator = null, $value = null)
 * @method Wrapper orWhere($column, $operator = null, $value = null)
 * @method Wrapper whereIn(string $column, array|self $values)
 * @method Wrapper orderBy($column, $direction = 'ASC')
 * @method Wrapper limit(int $limit): static
 * @method Wrapper offset(int $offset): static
 * @method Wrapper set(string|array $column, mixed $value = null)
 * @method Wrapper values(array $rows)
 * 
 * @see QueryBuilder para detalhes completos sobre o uso do método.
 * @see \SimplePhp\SimpleCrud\Core\SelectBuilder para métodos adicionais para SELECT.
 * @see \SimplePhp\SimpleCrud\Core\DeleteBuilder para métodos adicionais para DELETE.
 * @see \SimplePhp\SimpleCrud\Core\UpdateBuilder para métodos adicionais para UPDATE.
 * 
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
     * @return Wrapper
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
    public function execute(): QueryResult
    {
        return $this->executor->handle($this->builder);
    }

    /**
     * Executa a consulta SQL construída utilizando bindvalue() e retorna o resultado.
     * @return QueryResult
     */
    public function executeBind(): QueryResult
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
