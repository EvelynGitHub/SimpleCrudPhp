<?php

declare(strict_types=1);

namespace SimplePhp\SimpleCrud\Facades;

use PDO;
use SimplePhp\SimpleCrud\Contracts\CustomQuery;
use SimplePhp\SimpleCrud\Core\DeleteBuilder;
use SimplePhp\SimpleCrud\Core\InsertBuilder;
use SimplePhp\SimpleCrud\Core\RawQueryBuilder;
use SimplePhp\SimpleCrud\Core\SelectBuilder;
use SimplePhp\SimpleCrud\Core\UpdateBuilder;
use SimplePhp\SimpleCrud\Facades\Wrapper;
use SimplePhp\SimpleCrud\Infra\Database\PDOFactory;
use SimplePhp\SimpleCrud\UseCases\ExecuteQuery;

class DB
{
    protected static ?PDO $pdo = null;
    private static array $customQueries = [];

    private function __construct()
    {
    }

    /**
     * Substitui o PDO padrão para uma instância personalizada.
     * @param PDO $pdo
     * @return void
     */
    public static function connect(PDO $pdo): void
    {
        self::$pdo = $pdo;
    }

    public static function checkConnection(): array
    {
        try {
            self::ensureConnected();
            $driver = self::$pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
            $status = self::$pdo->query('SELECT 1')->fetchColumn() === '1' ? 'ok' : 'falha';

            return [
                'status' => $status,
                'driver' => $driver,
                'dsn' => self::$pdo->getAttribute(PDO::ATTR_CONNECTION_STATUS) ?? 'N/A',
                'version' => self::$pdo->getAttribute(PDO::ATTR_SERVER_VERSION) ?? 'N/A',
                'database' => getenv('DB_DATABASE') ?? 'desconhecido',
            ];
        } catch (\Throwable $e) {
            return [
                'status' => 'erro',
                'erro' => $e->getMessage(),
                'driver' => getenv('DB_CONNECTION') ?? 'não configurado',
                'banco' => getenv('DB_DATABASE') ?? 'desconhecido',
            ];
        }
    }

    protected static function ensureConnected(): void
    {
        if (!self::$pdo) {
            self::$pdo = PDOFactory::createFromEnv();
        }
    }

    public static function beginTransaction(): void
    {
        self::ensureConnected();
        self::$pdo->beginTransaction();
    }

    public static function commit(): void
    {
        self::ensureConnected();
        self::$pdo->commit();
    }

    public static function rollBack(): void
    {
        self::ensureConnected();
        self::$pdo->rollBack();
    }

    /** 
     * Verifica se uma transação está atualmente ativa no driver.
     */
    public static function inTransaction(): bool
    {
        self::ensureConnected();
        return self::$pdo->inTransaction();
    }

    /**
     * Constrói uma consulta Select
     * @param array $columns
     * @return Wrapper&SelectBuilder
     */
    public static function select(array $columns): Wrapper
    {
        self::ensureConnected();
        return new Wrapper(
            (new SelectBuilder())->select($columns),
            new ExecuteQuery(self::$pdo)
        );
    }

    /**
     * Insere dados na tabela especificada
     * @param string $table
     * @return Wrapper&InsertBuilder
     */
    public static function insert(string $table): Wrapper
    {
        self::ensureConnected();
        return new Wrapper(
            (new InsertBuilder())->table($table),
            new ExecuteQuery(self::$pdo)
        );
    }

    /**
     * Atualiza dados na tabela especificada
     * @param string $table
     * @return Wrapper&UpdateBuilder
     */
    public static function update(string $table): Wrapper
    {
        self::ensureConnected();
        return new Wrapper(
            (new UpdateBuilder())->from($table),
            new ExecuteQuery(self::$pdo)
        );
    }

    /**
     * Deleta dados na tabela especificada
     * @param string $table
     * @return Wrapper&DeleteBuilder
     */
    public static function delete(string $table): Wrapper
    {
        self::ensureConnected();
        return new Wrapper(
            (new DeleteBuilder())->from($table),
            new ExecuteQuery(self::$pdo)
        );
    }

    public static function query(string $sql, array $bindings = []): Wrapper
    {
        self::ensureConnected();
        return new Wrapper(
            new RawQueryBuilder($sql, $bindings),
            new ExecuteQuery(self::$pdo)
        );
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
     * @return Wrapper
     */
    public function customQuery(string $name, array $params = []): Wrapper
    {
        if (!isset(self::$customQueries[$name])) {
            throw new \Exception("Consulta '$name' não registrada.");
        }

        $queryInstance = clone self::$customQueries[$name]; // Clonamos para evitar alterar a instância global
        $queryInstance->setParams($params);

        $sql = $queryInstance->apply();
        $bindings = $queryInstance->getParams();

        self::ensureConnected();
        return new Wrapper(
            new RawQueryBuilder($sql, $bindings),
            new ExecuteQuery(self::$pdo)
        );
    }

}