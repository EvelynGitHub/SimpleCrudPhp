<?php

namespace Source\Crud;

use Connection;
use Exception;
use PDO;

abstract class Crud
{
    private $query = "";
    private $terms = [];
    private static $error;


    /**
     * @param table
     * @param data
     * @param columns
     * @return Crud
     */
    protected function insert(string $table, array $data, string $columns = ""): ?Crud
    {
        $amount = "";

        foreach ($data as $value) {
            $amount .= "?, ";
            array_push($this->terms, $value);
        }

        $amount = substr($amount, 0, -2);

        if ($columns) {
            $this->query .= " INSERT INTO $table ($columns) VALUES ($amount)";
        } else {
            $this->query .= " INSERT INTO $table VALUES ($amount)";
        }

        return $this;
    }

    /**
     * @param columns $columns = "id, nome, numero...etc";
     * Monta a primeira parte de um clausula SELECT 
     */
    protected function select(string $columns = "*"): ?Crud
    {
        $this->query .= " SELECT $columns";

        return $this;
    }

    /**
     * Atualiza o registro especificado
     * @param table nome da tabela que deseja atualizar
     * @param columns colunas para atualizar. Ex: "nmlogin = ?, cdpass=? " 
     * @param data valores para substituirem os '?' devem ser colocados na mesma ordem
     * @return Crud
     */
    protected function update(string $table, string $columns, array $data): ?Crud
    {
        $this->query .= " UPDATE $table SET $columns";

        foreach ($data as $value) {
            array_push($this->terms, $value);
        }

        return $this;
    }

    /**
     * @return Crud
     */
    protected function delete(): ?Crud
    {
        $this->query .= " DELETE";
        return $this;
    }

    /**
     * @param table
     * @return Crud
     */
    protected function from(string $table): ?Crud
    {
        $this->query .= " FROM $table";

        return $this;
    }

    /**
     * @param conditions column1, column2 || LIKE, AND, OR
     * @param values
     * @return Crud
     */
    protected function where(string $conditions, array $values = []): ?Crud
    {
        if(!empty($values)) {
            foreach ($values as $value) {
                array_push($this->terms, $value);
            }
        }        
        $this->query .= " WHERE $conditions";
        return $this;
    }


    /**
     * @param columns "column1, column2"
     * @param order "ASC|DESC"
     * @return Crud
     */
    protected function order(string $columns, string $order = "ASC"): ?Crud
    {
        $this->query .= " ORDER BY $columns $order ";
        // $this->query .= " ORDER BY $columns ";
        return $this;
    }

    /**
     * @param start 0
     * @param end 10
     * @return Crud
     */
    protected function limit(int $start = 0, int $end = 10): ?Crud
    {
        $this->query .= " LIMIT $start, $end";
        return $this;
    }

    /**
     * @param fetch fetch (retorna um objeto), fetchAll (retorna um array), rowCount (numero de linhas afetadas)
     * @param cleanQuery 
     * @return mixed
     */
    protected function execute(string $fetch = "", bool $cleanQuery = true)
    {
        try {

            $conn = Connection::getInstance();

            $stmt = $conn->prepare($this->query);

            foreach ($this->terms as $key => $val) {
                $stmt->bindValue($key + 1, $val, $this->bindType($val));
            }

            if ($cleanQuery) {
                $this->query = "";
                $this->terms = [];
            }

            if ($stmt->execute()) {

                if ($fetch != "") {
                    if ($fetch == "fetch") {
                        $rs = $stmt->fetch(PDO::FETCH_OBJ);
                    } else if ($fetch == "fetchAll") {
                        $rs = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    } else if ($fetch == "rowCount") {
                        $rs = $stmt->rowCount();
                    }
                    return $rs;
                }
                return true;
            } else {
                return false;
            }
        } catch (Exception $e) {
            self::$error = $e;
        }
    }

    /**
     * @param value
     * @return PDO::PARAM_* 
     */
    private function bindType($value)
    {
        $var_type = null;

        switch (true) {
            case is_bool($value):
                $var_type = PDO::PARAM_BOOL;
                break;
            case is_int($value):
                $var_type = PDO::PARAM_INT;
                break;
            case is_null($value):
                $var_type = PDO::PARAM_NULL;
                break;
            default:
                $var_type = PDO::PARAM_STR;
        }

        return $var_type;
    }

    /**
     * @return mixed
     */
    protected function getQuery()
    {
        return $this->query;
    }

    /**
     * @return mixed
     */
    protected static function getError()
    {
        return self::$error;
    }
}
