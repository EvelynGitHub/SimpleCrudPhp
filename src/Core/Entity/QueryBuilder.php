<?php

declare(strict_types=1);

namespace SimplePhp\SimpleCrud\Core\Entity;


class QueryBuilder
{
    private string $query = '';

    public function select(string $columns): void
    {
        $this->query .= " SELECT $columns";
    }

    public function from(string $table): void
    {
        $this->query .= " FROM $table";
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

    public function where(string $condition): void
    {
        // $this->query .= " WHERE $condition";

        if (!empty($values)) {
            foreach ($values as $value) {
                array_push($this->terms, $value);
            }
        }
        $this->query .= " WHERE $condition";
        // return $this;
    }

    public function getQuery(): string
    {
        return $this->query;
    }
}



// namespace SimplePhp\SimpleCrud\Infra\Database;

// use Exception;
// use PDO;
// use PDOException;

// class Crud
// {
//   private $query = "";
//   private $terms = [];
//   private static $error;

//   /**
//    * @param $table
//    * @param $data
//    * @return Crud
//    */
//   protected function insert(string $table, array $data): ?Crud
//   {
//     $columns = implode(",", array_keys($data));
//     $amount = implode(',', array_fill(0, count($data), '?'));

//     $this->addTerms($data);

//     $this->query .= " INSERT INTO $table ($columns) VALUES ($amount)";

//     return $this;
//   }

//   /**
//    * @param $columns $columns = "id, nome, numero...etc";
//    * Monta a primeira parte de um clausula SELECT
//    */
//   protected function select(string $columns = "*"): ?Crud
//   {
//     $this->query .= " SELECT $columns";

//     return $this;
//   }

//   /**
//    * Atualiza o registro especificado
//    * @param string $table nome da tabela que deseja atualizar
//    * @param string $columns colunas para atualizar. Ex: "nmlogin = ?, cdpass=? "
//    * @param array $data valores para substituirem os '?' devem ser colocados na mesma ordem
//    * @return Crud
//    */
//   protected function update(string $table, string $columns, array $data): ?Crud
//   {
//     $this->query .= " UPDATE $table SET $columns";

//     $this->addTerms($data);

//     return $this;
//   }

//   /**
//    * @return Crud
//    */
//   protected function delete(): ?Crud
//   {
//     $this->query .= " DELETE";
//     return $this;
//   }

//   /**
//    * @param string $table
//    * @return Crud
//    */
//   protected function from(string $table): ?Crud
//   {
//     $this->query .= " FROM $table";

//     return $this;
//   }

//   /**
//    * @param $conditions column1, column2 || LIKE, AND, OR
//    * @param $values
//    * @return Crud
//    */
//   protected function where(string $conditions, array $values = []): ?Crud
//   {
//     if (!empty($values)) {
//       foreach ($values as $value) {
//         array_push($this->terms, $value);
//       }
//     }
//     $this->query .= " WHERE $conditions";
//     return $this;
//   }


//   /**
//    * @param $query "SELECT * FROM foo WHERE foo_id = ?"
//    * @param $values "[1]"
//    * @return Crud
//    */
//   protected function query(string $query, array $values = []): ?Crud
//   {
//     $this->query = $query;

//     if (!empty($values)) {
//       foreach ($values as $value) {
//         array_push($this->terms, $value);
//       }
//     }
//     return $this;
//   }


//   /**
//    * @param string $columns "column1, column2"
//    * @param string $order "ASC|DESC"
//    * @return Crud
//    */
//   protected function order(string $columns, string $order = "ASC"): ?Crud
//   {
//     $this->query .= " ORDER BY $columns $order ";
//     // $this->query .= " ORDER BY $columns ";
//     return $this;
//   }


//   /**
//    * @param $start 0
//    * @param $end 10
//    * @return Crud
//    */
//   protected function limit(int $start = 0, int $end = 10): ?Crud
//   {
//     $this->query .= " LIMIT $start, $end";
//     return $this;
//   }

//   /**
//    * @param $name
//    * @param $params
//    * @return Crud
//    */
//   protected function call(string $name, array $params = []): ?Crud
//   {
//     if (!empty($params)) {
//       foreach ($params as $param) {
//         array_push($this->terms, $param);
//       }
//     }
//     $this->query .= " CALL $name";
//     return $this;
//   }

//   /**
//    * @param $fetch fetch (retorna um objeto), fetchAll (retorna um array), rowCount (numero de linhas afetadas)
//    * @param $cleanQuery
//    * @return mixed
//    */
//   protected function execute(string $fetch = "", bool $cleanQuery = true)
//   {
//     try {

//       $conn = Connection::getInstance();

//       $stmt = $conn->prepare($this->query);

//       foreach ($this->terms as $key => $val) {
//         $stmt->bindValue($key + 1, $val, $this->bindType($val));
//       }

//       if ($cleanQuery) {
//         $this->query = "";
//         $this->terms = [];
//       }

//       if ($stmt->execute()) {

//         if ($fetch != "") {
//           if ($fetch == "fetch") {
//             $rs = $stmt->fetch(PDO::FETCH_OBJ);
//           } else if ($fetch == "fetchAll") {
//             $rs = $stmt->fetchAll(PDO::FETCH_ASSOC);
//           } else if ($fetch == "rowCount") {
//             $rs = $stmt->rowCount();
//           } else if ($fetch == "lastId") {
//             $rs = $conn->lastInsertId();
//           }
//           return $rs;
//         }
//         return true;
//       } else {
//         return false;
//       }
//     } catch (ConnectionException $con) {
//       self::$error = $con->getError();
//       throw new Exception("Falha ao conectar com o Banco de Dados: " . $con->getMessage());
//     } catch (PDOException $e) {
//       self::$error = $e->getMessage();
//       throw new PDOException('Falha ao executar:' . self::$error);
//     } catch (Exception $e) {
//       self::$error = $e;
//     }
//   }

//   /**
//    * @param $value
//    * @return PDO::PARAM_*
//    */
//   private function bindType($value)
//   {
//     $var_type = null;

//     switch (true) {
//       case is_bool($value):
//         $var_type = PDO::PARAM_BOOL;
//         break;
//       case is_int($value):
//         $var_type = PDO::PARAM_INT;
//         break;
//       case is_null($value):
//         $var_type = PDO::PARAM_NULL;
//         break;
//       default:
//         $var_type = PDO::PARAM_STR;
//     }

//     return $var_type;
//   }

//   // protected function query(string $sql, array $data = []): ?Crud
//   // {
//   //     $this->query .= $sql;

//   //     $this->addTerms($data);

//   //     return $this;
//   // }


//   /**
//    * @return mixed
//    */
//   protected function getQuery()
//   {
//     return $this->query;
//   }

//   /**
//    * @return mixed
//    */
//   protected static function getError()
//   {
//     return self::$error;
//   }

//   private function addTerms(array $data)
//   {
//     foreach ($data as $value) {
//       array_push($this->terms, $value);
//     }
//   }
// }

