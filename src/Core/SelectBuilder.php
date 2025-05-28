<?php

declare(strict_types=1);

namespace SimplePhp\SimpleCrud\Core;

use SimplePhp\SimpleCrud\Contracts\BuilderInterface;

class SelectBuilder extends QueryBuilder implements BuilderInterface
{
    // protected $table;
    // protected $columns = '*';
    // protected $wheres = [];
    protected $orderBy = [];
    // protected ?int $limit = null;
    // protected ?int $offset = null;
    private array $joins = [];

    // public function select(...$columns)
    // {
    //     $this->columns = $columns;
    //     return $this;
    // }

    public function select(array|string ...$columns): static
    {
        $this->columns = is_array($columns[0]) ? $columns[0] : $columns;
        return $this;
    }


    public function from(string $table): static
    {
        $this->table = $table;
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

    public function orderBy($column, $direction = 'ASC')
    {
        $this->orderBy[] = "$column $direction";
        return $this;
    }

    /**
     * 
     * SELECT [colunas]
     * FROM [tabela]
     * [JOIN tipo JOIN outra_tabela ON condição_de_junção]
     * [WHERE condição]
     * [GROUP BY colunas]
     * [HAVING condição_agregada]
     * [ORDER BY colunas [ASC|DESC]]
     * [LIMIT número] [OFFSET número];
     * 
     * @return string
     */
    public function buildQuery()
    {
        $cols = implode(", ", $this->columns);

        $query = "SELECT {$cols} FROM {$this->table}";

        if (!empty($this->joins)) {
            foreach ($this->joins as $join) {
                $query .= " {$join['type']} JOIN {$join['table']} ON {$join['on']}";
            }
        }

        if (!empty($this->wheres)) {
            $query .= ' WHERE ' . $this->compileWheres();
        }

        if (!empty($this->orderBy)) {
            $query .= ' ORDER BY ' . implode(', ', $this->orderBy);
        }

        if (!empty($this->limit)) {
            $query .= ' LIMIT ' . $this->limit;
            if (!empty($this->offset)) {
                $query .= ' OFFSET ' . $this->offset;
            }
        }

        return $query;
    }

    public function getSql(): string
    {
        return $this->buildQuery();
    }

    // public function getBindings(): array
    // {
    //     $bindings = [];

    //     foreach ($this->wheres as $where) {
    //         $bindings = array_merge($bindings, is_array($where['value']) ? $where['value'] : [$where['value']]);
    //     }

    //     return $bindings;
    // }

}