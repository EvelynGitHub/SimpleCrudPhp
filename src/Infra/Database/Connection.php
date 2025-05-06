<?php

declare(strict_types=1);

namespace SimplePhp\SimpleCrud\Infra\Database;

use PDO;
use PDOException;
use SimplePhp\SimpleCrud\Core\Interfaces\DatabaseConnectionInterface;

class Connection implements DatabaseConnectionInterface
{
    private static PDO $instance;
    private static $error;

  // private function __construct()
  // {
  // }

    public static function getInstance(): PDO
    {
        if (empty(self::$instance)) {
            try {
                $database = DATABASE["driver"] . ":host=" . DATABASE["host"] . ";dbname=" . DATABASE["dbname"] . ";port=" . DATABASE["port"];

                if (DATABASE["driver"] == "sqlite") {
                    $database = 'sqlite:' . DATABASE['dbname'] . ".sqlite";
                }

                self::$instance = new PDO(
                    $database,
                    DATABASE["username"],
                    DATABASE["passwd"],
                    DATABASE["options"]
                );
            } catch (PDOException $exception) {
                self::$error = $exception;

                throw new ConnectionException(
                    $exception->getMessage(),
                    $exception->getCode()
                );
            }
        }

        return self::$instance;
    }

    public static function getError()
    {
        return self::$error;
    }
}
