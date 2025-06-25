<?php

declare(strict_types=1);

namespace SimplePhp\SimpleCrud\Core;

use SimplePhp\SimpleCrud\Contracts\BuilderInterface;

class DeleteBuilder extends QueryBuilder implements BuilderInterface
{
    protected string $table;

    public function from(string $table): static
    {
        $this->table = $table;
        return $this;
    }

    private function build(): string
    {
        $hasJoin = !empty($this->joins);
        $hasWhere = !empty($this->wheres);
        $hasLimit = !empty($this->limit);
        $hasOffset = !empty($this->offset);

        if (empty($this->table)) {
            throw new \RuntimeException('Especifique a Tabela para o DELETE.');
        }

        if ($hasJoin || $hasOffset || $hasLimit) {
            throw new \RuntimeException(
                'DELETE com JOINs, LIMIT ou OFFSET não é suportado.
                Use uma subquery no WHERE ou query livre query().
                Não esqueça de usar os bindings corretamente.'
            );
        }

        // Montagem básica
        $query = "DELETE FROM {$this->table}";

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