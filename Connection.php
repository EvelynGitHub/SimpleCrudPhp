<?php

//use PDOException;
class Connection
{
    private static $instance;
    private static $error;

    public static function getInstance()
    {
        if(empty(self::$instance)) {
            try {

                self::$instance = new PDO(
                    DATABASE["driver"] . ":host=" . DATABASE["host"] . ";dbname=" . DATABASE["dbname"] . ";port=" . DATABASE["port"],
                    DATABASE["username"],
                    DATABASE["passwd"],
                    DATABASE["options"]
                );

            } catch(PDOException $exception) {
                self::$error = $exception;
            }
        }

        return self::$instance;
    }

    public static function getError()
    {
        return self::$error;
    }
}