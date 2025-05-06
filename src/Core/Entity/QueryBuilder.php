<?php

declare(strict_types=1);

namespace SimplePhp\SimpleCrud\Core\Entity;

use SimplePhp\SimpleCrud\Core\Interfaces\CustomQuery;
use SimplePhp\SimpleCrud\Infra\Database\Crud;


class QueryBuilder
{
    protected static ?QueryBuilder $instance = null;
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

        return self::$instance;
    }

    public static function newInstance(): QueryBuilder
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

    /**
     * Prepara a query customizada instanciada anteriormente em registerQuery()
     * @param string $name Mesmo nome usado no registerQuery()
     * @param array $params Dados que serão usados para bind
     * @throws \Exception
     * @return QueryBuilder
     */
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
            if ($param instanceof Crud) {
                $condition = str_replace("?", "(" . $param->getQueryBuilder()->getQuery() . ")", $condition);
                $this->params = array_merge($this->params, $param->getQueryBuilder()->getParams());
            } else {
                $this->params = array_merge($this->params, $params);
            }
        }
        // $this->query .= " WHERE " . vsprintf(str_replace("?", "%s", $condition), $params);
        $this->query .= " WHERE $condition";
        return $this;
    }

    public function insert(string $table, array|Crud $data, ?string $columns = null): self
    {
        if (is_array($data)) {
            $columns = implode(', ', array_keys($data));
            $values = implode(', ', array_map(fn($v) => "'$v'", array_values($data)));
            $this->query = "INSERT INTO $table ($columns) VALUES ($values)";

            return $this;
        }

        if ($data instanceof Crud) {
            $values = $data->getQueryBuilder()->getQuery();

            $this->params = array_merge($this->params, $data->getQueryBuilder()->getParams());

            $this->query = "INSERT INTO $table ($columns) $values";
        }


        // throw new \Exception("Consulta não registrada.");
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


    public function update(string $table, array $data): self
    {
        $callback = fn(string $k, string $v): string => "$k = :$k";

        $columns = array_map($callback, array_keys($data), array_values($data));

        // $columns = array_map(fn($k, $v): string => "$k = :$k", $data);

        $set = implode(', ', $columns);

        $this->params = array_merge($this->params, $data);

        $this->query = "UPDATE $table SET $set";
        // $this->query .= " UPDATE $table SET $columns";

        // $this->addTerms($data);

        return $this;
    }


    /**
     * @param $query "SELECT * FROM foo WHERE foo_id = ?"
     * @param $values "[1]"
     * @return self
     */
    protected function query(string $query, array $values = []): self
    {
        $this->query = $query;

        if (!empty($values)) {
            foreach ($values as $value) {
                array_push($this->params, $value);
            }
        }
        return $this;
    }


    /**
     * @param string $columns "column1, column2"
     * @param string $order "ASC|DESC"
     * @return self
     */
    public function order(string $columns, string $order = "ASC"): self
    {
        $this->query .= " ORDER BY $columns $order ";
        // $this->query .= " ORDER BY $columns ";
        return $this;
    }


    public function group(array $columns): self
    {
        $value = implode(" ,", $columns);
        $this->query .= " GROUP BY $value ";
        return $this;
    }


    /**
     * @param $start 0
     * @param $end 10
     * @return self
     */
    protected function limit(int $start = 0, int $end = 10): self
    {
        $this->query .= " LIMIT $start, $end";
        return $this;
    }

    /**
     * @param $name
     * @param $params
     * @return self
     */
    protected function call(string $name, array $params = []): self
    {
        if (!empty($params)) {
            foreach ($params as $param) {
                array_push($this->params, $param);
            }
        }
        $this->query .= " CALL $name";
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

