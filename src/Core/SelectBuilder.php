<?php

declare(strict_types=1);

namespace SimplePhp\SimpleCrud\Core;

use SimplePhp\SimpleCrud\Contracts\BuilderInterface;

class SelectBuilder extends QueryBuilder implements BuilderInterface
{
    protected string $alias;
    protected array $groupBy = [];

    public function aliasSubQuery(string $name): void
    {
        $this->alias = $name;
    }

    public function select(array|string ...$columns): static
    {
        $columnsToProcess = (is_array($columns[0]) && count($columns) === 1) ? $columns[0] : $columns;

        if (is_array($columnsToProcess)) {
            foreach ($columnsToProcess as $key => $column) {
                if ($column instanceof SelectBuilder) {
                    $as = $column->alias ?: "sub_query_$key";
                    $columnsToProcess[$key] = "({$column->getSql()}) AS {$as}";
                    $this->bindings = array_merge($column->getBindings(), $this->bindings);
                }
            }
        }
        $this->columns = $columnsToProcess;
        return $this;
    }


    /**
     * Define a table usada no from do select
     * @param string $table
     * @return static
     */
    public function from(string $table): static
    {
        return parent::from($table);
    }

    public function group(array $columns): static
    {
        $this->groupBy = $columns;
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

        if (!empty($this->groupBy)) {
            $query .= ' GROUP BY ' . implode(', ', $this->groupBy);
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