<?php

declare(strict_types=1);

namespace SimplePhp\SimpleCrud\Core;

use SimplePhp\SimpleCrud\Contracts\BuilderInterface;

class UpdateBuilder extends QueryBuilder implements BuilderInterface
{
    protected string $table;
    protected array $sets = [];

    public function table(string $table): static
    {
        $this->table = $table;
        return $this;
    }

    /**
     * Define a coluna e o valor a ser atualizado.
     * 
     * @param string|array $column Nome da coluna ou array de colunas e valores
     * @param mixed $value Valor a ser atribuído à coluna. Ignorado se $column for um array.
     * @return $this
     */
    public function set(string|array $column, mixed $value = null): static
    {
        if (is_array($column)) {
            foreach ($column as $col => $val) {
                $this->sets[] = "{$col} = ?";
                $this->bindings[] = $val;
            }
            return $this;
        }

        if ($value instanceof BuilderInterface) {
            $this->sets[] = "{$column} = ({$value->getSql()})";
            $this->bindings = array_merge($value->getBindings(), $this->bindings);
            return $this;
        }

        $this->sets[] = "{$column} = ?";
        $this->bindings[] = $value;
        return $this;
    }

    private function build(): string
    {
        $hasJoin = !empty($this->joins);
        $hasWhere = !empty($this->wheres);
        $hasLimit = !empty($this->limit);
        $hasOffset = !empty($this->offset);

        if (empty($this->table)) {
            throw new \RuntimeException('Especifique a Tabela para o UPDATE.');
        }

        if (empty($this->sets)) {
            throw new \RuntimeException('Especifique ao menos uma coluna no SET.');
        }

        if ($hasJoin || $hasLimit || $hasOffset) {
            throw new \RuntimeException(
                'UPDATE com JOINs, LIMIT ou OFFSET não é suportado nativamente por todos os bancos.
            Use subquery no WHERE ou query livre com query().
            Não esqueça de usar os bindings corretamente.'
            );
        }

        $set = implode(', ', $this->sets);
        $query = "UPDATE {$this->table} SET {$set}";

        if ($hasWhere) {
            $query .= ' WHERE ' . $this->compileWheres();
        }

        return $query;
    }

    public function getSql(): string
    {
        return $this->build();
    }
}