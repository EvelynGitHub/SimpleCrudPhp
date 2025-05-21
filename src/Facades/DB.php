<?php

declare(strict_types=1);

use SimplePhp\SimpleCrud\Core\InsertBuilder;
use SimplePhp\SimpleCrud\Core\RawQueryBuilder;
use SimplePhp\SimpleCrud\Core\SelectBuilder;
use SimplePhp\SimpleCrud\Facades\InsertBuilderWrapper;
use SimplePhp\SimpleCrud\Facades\SelectBuilderWrapper;
use SimplePhp\SimpleCrud\Facades\Wrapper;
use SimplePhp\SimpleCrud\UseCases\ExecuteInsert;
use SimplePhp\SimpleCrud\UseCases\ExecuteQuery;

class DB
{
    // protected static $instance;
    protected static PDO $pdo;

    public static function connect(PDO $pdo): void
    {
        self::$pdo = $pdo;
    }

    // Exemplos de métodos que podem ser implementados
    // public static function select(array $columns): SelectBuilderWrapper
    // {
    //     return new SelectBuilderWrapper(
    //         new SelectBuilder()->select($columns),
    //         new ExecuteQuery(self::$pdo)
    //     );
    // }

    public static function select(array $columns): Wrapper
    {
        return new Wrapper(
            new SelectBuilder()->select($columns),
            new ExecuteQuery(self::$pdo)
        );
    }

    // Lógica para executar uma consulta INSERT usando o builder
    public static function insert(string $table): InsertBuilderWrapper
    {
        return new InsertBuilderWrapper(
            new InsertBuilder($table),
            new ExecuteInsert(self::$pdo)
        );
    }

    public function update($builder)
    {
        // Lógica para executar uma consulta UPDATE usando o builder
    }

    public function delete($builder)
    {
        // Lógica para executar uma consulta DELETE usando o builder
    }

    public static function query(string $sql, array $bindings = []): RawQueryBuilder
    {
        return new RawQueryBuilder($sql, $bindings);
    }

}