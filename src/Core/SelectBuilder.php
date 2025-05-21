<?php

declare(strict_types=1);

namespace SimplePhp\SimpleCrud\Core;

use SimplePhp\SimpleCrud\Contracts\BuilderInterface;

class SelectBuilder implements BuilderInterface
{
    protected $table;
    protected $columns = '*';
    protected $wheres = [];
    protected $orderBy = [];

    public function select(...$columns)
    {
        $this->columns = $columns;
        return $this;
    }

    public function from(string $table)
    {
        $this->table = $table;
        return $this;
    }

    // public function where($condition)
    // {
    //     $this->wheres[] = $condition;
    //     return $this;
    // }

    public function where(string $column, string $operator, mixed $value): static
    {
        if ($value instanceof SelectBuilder) {
            $sql = "$column $operator (" . $value->getSql() . ")";
            $bindings = $value->getBindings();
        } else {
            $sql = "$column $operator ?";
            $bindings = [$value];
        }

        $this->wheres[] = [
            'sql' => $sql,
            'value' => $bindings
        ];

        return $this;
    }


    public function orderBy($column, $direction = 'ASC')
    {
        $this->orderBy[] = "$column $direction";
        return $this;
    }

    public function buildQuery()
    {
        $cols = implode(", ", $this->columns);

        $query = "SELECT {$cols} FROM {$this->table}";

        if (!empty($this->wheres)) {
            $query .= ' WHERE ' . implode(' AND ', $this->wheres);
        }

        if (!empty($this->orderBy)) {
            $query .= ' ORDER BY ' . implode(', ', $this->orderBy);
        }

        return $query;
    }

    public function getSql(): string
    {
        return $this->buildQuery();
    }

    // public function getBindings(): array
    // {
    //     return array_column($this->wheres, 'value');
    // }

    public function getBindings(): array
    {
        $bindings = [];

        foreach ($this->wheres as $where) {
            $bindings = array_merge($bindings, is_array($where['value']) ? $where['value'] : [$where['value']]);
        }

        return $bindings;
    }

}