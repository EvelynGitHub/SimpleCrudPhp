<?php

declare(strict_types=1);

namespace SimplePhp\SimpleCrud\Core;

use SimplePhp\SimpleCrud\Contracts\BuilderInterface;

class InsertBuilder implements BuilderInterface
{
    protected $table;
    protected $values = [];

    public function __construct(string $table)
    {
        $this->table = $table;
    }

    public function table($table)
    {
        $this->table = $table;
        return $this;
    }

    public function values(array $data): static
    {
        $this->values = $data;
        return $this;
    }

    public function build()
    {
        if (empty($this->table) || empty($this->values)) {
            throw new \Exception("Table and values must be set before building the query.");
        }

        $columns = implode(", ", array_keys($this->values));
        $placeholders = implode(", ", array_fill(0, count($this->values), '?'));

        return "INSERT INTO {$this->table} ({$columns}) VALUES ({$placeholders})";
    }

    public function getSql(): string
    {
        return $this->build();
    }

    public function getBindings(): array
    {
        return array_values($this->values);
    }
}