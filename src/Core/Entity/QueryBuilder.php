<?php

declare(strict_types=1);

namespace SimplePhp\SimpleCrud\Core\Entity;

use SimplePhp\SimpleCrud\Core\Interfaces\CustomQuery;



class QueryBuilder
{
    protected static $instance = null;
    protected string $query;
    protected array $params = [];
    private static array $customQueries = [];


    /**
     * Impedindo new QueryBuilder fora da própria classe
     */
    private function __construct()
    {
    }

    /**
     * Utilizando padrão Singleton
     * @return QueryBuilder
     */
    public static function getInstance()
    {
        if (!self::$instance) {
            self::$instance = new self();
        }
        // return self::$instance;


        // return self::$instance->reset();
        self::$instance = new self();

        return self::$instance;
    }

    private static function newInstance(): QueryBuilder
    {
        return new self();
    }

    public function reset(): self
    {
        $this->query = "";
        $this->params = [];
        return $this;
    }

    public static function registerQuery(string $name, CustomQuery $queryInstance): void
    {
        self::$customQueries[$name] = $queryInstance;
    }

    public function customQuery(string $name, array $params = []): self
    {
        if (!isset(self::$customQueries[$name])) {
            throw new \Exception("Consulta '$name' não registrada.");
        }

        $queryInstance = clone self::$customQueries[$name]; // Clonamos para evitar alterar a instância global
        $queryInstance->setParams($params);

        $this->query = $queryInstance->apply();
        $this->params = $queryInstance->getParams();
        return $this;
    }


    // public function select(string $columns): void
    // {
    //     $this->query .= " SELECT $columns";
    // }
    public function select(...$columns): QueryBuilder
    {
        $columns = implode(", ", $columns);
        $this->query = "SELECT $columns";
        return $this;
    }


    public function from(string $table): QueryBuilder
    {
        $this->query .= " FROM $table";
        return $this;
    }

    public function join($table, $onCondition): QueryBuilder
    {
        $this->query .= " JOIN $table ON $onCondition";
        return $this;
    }

    public function where($condition, $params = [])
    {
        foreach ($params as &$param) {
            if ($param instanceof QueryBuilder) {
                // $subQuery = self::newInstance()
                //     // ->select($params->query) // Copia a query sem afetar a principal
                //     ->getQuery();
                // $condition = str_replace("?", "($subQuery)", $condition);

                $condition = str_replace("?", "(" . $param->getQuery() . ")", $condition);
                $this->params = array_merge($this->params, $param->getParams());


            } else {
                $this->params = array_merge($this->params, $params);
            }
        }
        // $this->query .= " WHERE " . vsprintf(str_replace("?", "%s", $condition), $params);
        $this->query .= " WHERE $condition";
        return $this;
    }

    public function insert(string $table, array $data): self
    {
        $columns = implode(', ', array_keys($data));
        $values = implode(', ', array_map(fn($v) => "'$v'", array_values($data)));
        $this->query = "INSERT INTO $table ($columns) VALUES ($values)";

        // $columns = implode(",", array_keys($data));
        // $amount = implode(',', array_fill(0, count($data), '?'));

        // $this->addTerms($data);

        // $this->query .= " INSERT INTO $table ($columns) VALUES ($amount)";

        return $this;
    }

    public function insertSelect($table)
    {
        $this->query = "INSERT INTO $table";
        return $this;
    }

    public function columns(...$columns)
    {
        $this->query .= " (" . implode(", ", $columns) . ")";
        return $this;
    }

    public function selectQuery(QueryBuilder $subQuery)
    {
        $this->query .= " " . $subQuery->getQuery();
        $this->params = array_merge($this->params, $subQuery->getParams());
        return $this;
    }


    public function update(string $table, string $columns): self
    {
        // $set = implode(', ', array_map(fn($k, $v) => "$k = '$v'", array_keys($data), $data));
        // $this->query = "UPDATE $table SET $set";
        $this->query .= " UPDATE $table SET $columns";

        // $this->addTerms($data);

        return $this;
    }

    public function delete(string $table): void
    {
        $this->query = "DELETE FROM $table";
    }

    public function getQuery(): string
    {
        return $this->query;
    }

    public function getParams(): array
    {
        return $this->params;
    }
}

