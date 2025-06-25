<?php

declare(strict_types=1);

namespace SimplePhp\SimpleCrud\Core;

use SimplePhp\SimpleCrud\Contracts\BuilderInterface;

class InsertBuilder implements BuilderInterface
{
    protected string $table = '';
    protected string $subquery = '';
    protected array $columns = [];
    protected array $rows = [];
    protected array $bindings = [];

    /**
     * Define a tabela onde os dados serão inseridos.
     *
     * @param string $table Nome da tabela
     * @return static
     */
    public function table(string $table): static
    {
        $this->table = $table;
        return $this;
    }

    /**
     * Define os valores a serem inseridos.
     *
     * @param array $rows Linhas de dados a serem inseridas. Ex: [['coluna1' => 'valor1', 'coluna2' => 'valor2'], ...]
     * @return void
     * @throws \RuntimeException Se não forem informadas linhas para o INSERT.
     * @throws \InvalidArgumentException Se o número de valores não bater com as colunas.
     */
    public function values(array $rows): void
    {
        if (empty($rows)) {
            throw new \RuntimeException("Obrigatório informar linhas para o INSERT.");
        }

        $isArraySimple = array_keys($rows) !== range(0, count($rows) - 1);

        if ($isArraySimple) {
            // Se é simples, passa a ser multi linha
            $rows = [$rows];
        }

        $quantity = count($rows[0]);
        $columns = array_keys($rows[0]);

        $this->columns = $columns;

        foreach ($rows as $row) {
            if (count($row) !== $quantity) {
                throw new \InvalidArgumentException('Número de valores não bate com as colunas.');
            }
            $this->rows[] = $row;
            $this->bindings = array_merge($this->bindings, array_values($row));
        }

    }

    /**
     * Define os valores a serem inseridos, permitindo uma subquery como fonte.
     *
     * @param array $columns Colunas a serem inseridas. Ex: ['coluna1', 'coluna2']
     * @param BuilderInterface $data Subquery que retorna os dados a serem inseridos
     * @return void
     */
    public function valuesWhitSelect(array $columns, BuilderInterface $data): void
    {
        $this->columns = $columns;

        if (empty($data->getSql())) {
            throw new \InvalidArgumentException('A subquery deve retornar dados válidos.');
        }

        $this->subquery = $data->getSql();
        $this->bindings = array_merge($this->bindings, $data->getBindings());
    }


    public function build(): string
    {
        if (empty($this->table) || empty($this->columns)) {
            throw new \RuntimeException("Obrigatório informar a tabela e as colunas para o INSERT.");
        }

        $columns = implode(", ", array_values($this->columns));

        if (empty($this->subquery)) {
            $placeholders = '(' . implode(', ', array_fill(0, count($this->columns), '?')) . ')';
            $allPlaceholders = implode(', ', array_fill(0, count($this->rows), $placeholders));
            return "INSERT INTO {$this->table} ({$columns}) VALUES {$allPlaceholders}";
        }

        return "INSERT INTO {$this->table} ({$columns}) {$this->subquery}";
    }

    public function getSql(): string
    {
        return $this->build();
    }

    public function getBindings(): array
    {
        return $this->bindings;
    }
}