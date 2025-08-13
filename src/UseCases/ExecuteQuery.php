<?php

declare(strict_types=1);

namespace SimplePhp\SimpleCrud\UseCases;

use PDO;
use SimplePhp\SimpleCrud\Contracts\BuilderInterface;
use SimplePhp\SimpleCrud\Contracts\ExecutableInterface;

class ExecuteQuery implements ExecutableInterface
{
    public function __construct(private PDO $pdo)
    {
    }

    public function handle(BuilderInterface $builder): QueryResult
    {
        $stmt = $this->pdo->prepare($builder->getSql());
        $stmt->execute($builder->getBindings());

        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return new QueryResult(
            fetchAll: $results,
            fetch: $results[0] ?? null,
            rowCount: $stmt->rowCount(),
            lastInsertId: $this->pdo->lastInsertId() ?: null
        );
    }

    public function execute(BuilderInterface $builder): QueryResult
    {
        $stmt = $this->pdo->prepare($builder->getSql());

        foreach ($builder->getBindings() as $key => $val) {
            if (is_string($key)) {
                $stmt->bindValue(":$key", $val, $this->bindType($val));
            } else {
                $stmt->bindValue($key + 1, $val, $this->bindType($val));
            }
        }

        $stmt->execute();

        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return new QueryResult(
            fetchAll: $results,
            fetch: $results[0] ?? null,
            rowCount: $stmt->rowCount(),
            lastInsertId: $this->pdo->lastInsertId() ?: null
        );
    }

    /**
     * @param $value
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


    // if ($fetch != "") {
    //       if ($fetch == "fetch") {
    //         $rs = $stmt->fetch(PDO::FETCH_OBJ);
    //       } else if ($fetch == "fetchAll") {
    //         $rs = $stmt->fetchAll(PDO::FETCH_ASSOC);
    //       } else if ($fetch == "rowCount") {
    //         $rs = $stmt->rowCount();
    //       } else if ($fetch == "lastId") {
    //         $rs = $conn->lastInsertId();
    //       }



    public function lastId(BuilderInterface $builder): int|null
    {
        $stmt = $this->pdo->prepare($builder->getSql());
        $stmt->execute($builder->getBindings());

        return $this->pdo->lastInsertId() ?: null;
    }
}
