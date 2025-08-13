<?php

declare(strict_types=1);

namespace SimplePhp\SimpleCrud\Core;

use SimplePhp\SimpleCrud\Contracts\BuilderInterface;

class DeleteBuilder extends QueryBuilder implements BuilderInterface
{
    /**
     * Define ou sobrescreve a tabela da qual os registros serão excluídos.
     *
     * É obrigatório definir a tabela para a consulta DELETE.
     * Este método sobrescreve qualquer tabela definida anteriormente.
     * Por exemplo, ao usar `DB::delete('t1')->from('t2')`, a tabela utilizada será 't2'.
     *
     * @param string $table A tabela a ser usada na cláusula DELETE FROM.
     * @return static
     */
    public function from(string $table): static
    {
        return parent::from($table);
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