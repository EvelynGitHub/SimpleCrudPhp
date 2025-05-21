<?php

declare(strict_types=1);

namespace SimplePhp\SimpleCrud\Core;

use SimplePhp\SimpleCrud\Contracts\BuilderInterface;

class UpdateBuilder implements BuilderInterface //extends QueryBuilder
{
    protected $table;
    protected $values = [];
    protected $conditions = [];

    public function table($table)
    {
        $this->table = $table;
        return $this;
    }

    public function set($column, $value)
    {
        $this->values[$column] = $value;
        return $this;
    }

    public function where($condition)
    {
        $this->conditions[] = $condition;
        return $this;
    }

    public function build()
    {
        if (empty($this->table) || empty($this->values)) {
            throw new \Exception("Table and values must be set for an UPDATE query.");
        }

        $setClause = implode(", ", array_map(function ($column) {
            return "$column = :$column";
        }, array_keys($this->values)));

        $query = "UPDATE {$this->table} SET $setClause";

        if (!empty($this->conditions)) {
            $query .= " WHERE " . implode(" AND ", $this->conditions);
        }

        return $query;
    }

    public function getSql(): string
    {
        return $this->build();
    }

    public function getBindings(): array
    {
        return [];
    }
}