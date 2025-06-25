<?php

namespace SimplePhp\SimpleCrud\Core;

use Closure;
use SimplePhp\SimpleCrud\Helpers\SQLUtils;

/**
 * Classe base para construção de queries SQL.
 * Fornece métodos comuns para builders de SELECT, INSERT, UPDATE e DELETE.
 */
abstract class QueryBuilder
{
    protected string $table = '';
    protected array $joins = [];
    protected array $columns = [];
    protected array $wheres = [];
    protected array $bindings = [];
    protected array $orderBy = [];
    protected ?int $limit = null;
    protected ?int $offset = null;

    public function join(string $table, string $onCondition, string $type = 'INNER'): static
    {
        $this->joins[] = [
            'table' => $table,
            'on' => $onCondition,
            'type' => $type
        ];

        return $this;
    }

    public function where($column, $operator = null, $value = null): static
    {
        return $this->addWhere('AND', $column, $operator, $value);
    }

    public function orWhere($column, $operator = null, $value = null): static
    {
        return $this->addWhere('OR', $column, $operator, $value);
    }

    public function whereIn(string $column, array|self $values): static
    {
        if ($values instanceof self) {
            $val = '(' . $values->getSql() . ')';
            $this->bindings = array_merge($this->bindings, $values->getBindings());
        } else {
            $placeholders = [];
            foreach ($values as $v) {
                $placeholders[] = $this->quote($v);
            }
            $val = '(' . implode(', ', $placeholders) . ')';
        }
        $this->wheres[] = ['sql' => "$column IN $val", 'boolean' => 'AND'];
        return $this;
    }

    protected function addWhere($boolean, $column, $operator = null, $value = null): static
    {
        if ($column instanceof Closure) {
            $nested = new static();
            $column($nested);
            $sql = $nested->compileWheres();
            $this->bindings = array_merge($this->bindings, $nested->getBindings());
            $this->wheres[] = ['sql' => "($sql)", 'boolean' => $boolean];
        } elseif (!is_null($column) && !is_null($operator) && is_null($value)) {
            $this->wheres[] = ['sql' => "$column = " . $this->quote($operator), 'boolean' => $boolean];
        } elseif (!is_null($column) && !is_null($operator) && !is_null($value)) {
            $this->wheres[] = ['sql' => "$column $operator " . $this->quote($value), 'boolean' => $boolean];
        } elseif (is_array($column) && is_null($operator) && is_null($value)) {
            $nested = new static();
            SQLUtils::applyFilters($nested, $column);
            $sql = $nested->compileWheres();
            $this->bindings = array_merge($this->bindings, $nested->getBindings());
            $this->wheres[] = ['sql' => $sql, 'boolean' => null];
        } else {
            // não tem bindings, apenas adiciona o SQL diretamente
            $this->wheres[] = ['sql' => $column, 'boolean' => $boolean];
        }
        return $this;
    }

    protected function compileWheres(): string
    {
        if (empty($this->wheres))
            return '';
        $sql = $this->wheres[0]['sql'];
        for ($i = 1; $i < count($this->wheres); $i++) {
            $sql .= ' ' . $this->wheres[$i]['boolean'] . ' ' . $this->wheres[$i]['sql'];
        }
        return $sql;
    }

    protected function quote($value): string
    {
        if ($value instanceof self) {
            $sql = $value->getSql();
            $this->bindings = array_merge($this->bindings, $value->getBindings());
            return "($sql)";
        }
        // Adiciona o valor ao array de bindings e retorna o placeholder
        $this->bindings[] = $value;
        return '?';
    }

    public function orderBy($column, $direction = 'ASC')
    {
        $this->orderBy[] = "$column $direction";
        return $this;
    }

    public function limit(int $limit): static
    {
        $this->limit = $limit;
        return $this;
    }

    public function offset(int $offset): static
    {
        $this->offset = $offset;
        return $this;
    }

    public function getBindings(): array
    {
        return $this->bindings;
    }

    /**
     * Método que deve ser implementado nas classes filhas para gerar o SQL final.
     */
    abstract public function getSql(): string;

    /**
     * Limpa os dados do builder para reutilização.
     */
    public function reset(): void
    {
        $this->table = '';
        $this->columns = [];
        $this->wheres = [];
        $this->bindings = [];
        $this->order = [];
        $this->limit = null;
        $this->offset = null;
    }
}