<?php

declare(strict_types=1);

namespace SimplePhp\SimpleCrud\Core;

use SimplePhp\SimpleCrud\Contracts\BuilderInterface;

class DeleteBuilder implements BuilderInterface
{
    protected $table;
    protected $conditions = [];

    public function from($table)
    {
        $this->table = $table;
        return $this;
    }

    public function where($condition)
    {
        $this->conditions[] = $condition;
        return $this;
    }

    private function build()
    {
        $query = "DELETE FROM " . $this->table;

        if (!empty($this->conditions)) {
            $query .= " WHERE " . implode(' AND ', $this->conditions);
        }

        return $query;
    }

    public function getSql(): string
    {
        return $this->build();
    }

    public function getBindings(): array
    {
        // Aqui você pode retornar os bindings, se necessário.
        return [];
    }
}