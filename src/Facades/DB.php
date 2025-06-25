<?php

declare(strict_types=1);

use SimplePhp\SimpleCrud\Core\DeleteBuilder;
use SimplePhp\SimpleCrud\Core\InsertBuilder;
use SimplePhp\SimpleCrud\Core\RawQueryBuilder;
use SimplePhp\SimpleCrud\Core\SelectBuilder;
use SimplePhp\SimpleCrud\Core\UpdateBuilder;
use SimplePhp\SimpleCrud\Facades\Wrapper;
use SimplePhp\SimpleCrud\UseCases\ExecuteQuery;

class DB
{
    protected static PDO $pdo;

    public static function connect(PDO $pdo): void
    {
        self::$pdo = $pdo;
    }

    public static function select(array $columns): Wrapper
    {
        return new Wrapper(
            new SelectBuilder()->select($columns),
            new ExecuteQuery(self::$pdo)
        );
    }

    public static function insert(string $table): Wrapper
    {
        return new Wrapper(
            new InsertBuilder()->table($table),
            new ExecuteQuery(self::$pdo)
        );
    }

    public function update(string $table): Wrapper
    {
        return new Wrapper(
            new UpdateBuilder()->table($table),
            new ExecuteQuery(self::$pdo)
        );
    }

    public function delete(string $table): Wrapper
    {
        return new Wrapper(
            new DeleteBuilder()->from($table),
            new ExecuteQuery(self::$pdo)
        );
    }

    public static function query(string $sql, array $bindings = []): Wrapper
    {
        // return new RawQueryBuilder($sql, $bindings);
        return new Wrapper(
            new RawQueryBuilder($sql, $bindings),
            new ExecuteQuery(self::$pdo)
        );
    }

}