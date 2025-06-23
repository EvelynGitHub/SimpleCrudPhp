<?php

declare(strict_types=1);

namespace SimplePhp\SimpleCrud\Core;

use SimplePhp\SimpleCrud\Contracts\BuilderInterface;

class SelectBuilder extends QueryBuilder implements BuilderInterface
{
    protected array $orderBy = [];
    private array $joins = [];
    protected string $alias;

    public function aliasSubQuery(string $name): void
    {
        $this->alias = $name;
    }

    public function select(array|string ...$columns): static
    {
        if (is_array($columns)) {
            foreach ($columns[0] as $key => $column) {
                if ($column instanceof SelectBuilder) {
                    $as = $column->alias ?: "sub_query_$key";
                    $columns[0][$key] = "({$column->getSql()}) AS {$as}";
                    $this->bindings = array_merge($column->getBindings(), $this->bindings);
                }
            }
        }
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

    public function join(string $table, string $onCondition, string $type = 'INNER'): static
    {
        $this->joins[] = [
            'table' => $table,
            'on' => $onCondition,
            'type' => $type
        ];

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
}