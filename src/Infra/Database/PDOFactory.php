<?php

declare(strict_types=1);

namespace SimplePhp\SimpleCrud\Infra\Database;

use PDO;
use RuntimeException;

class PDOFactory
{
    public static function createFromEnv(): PDO
    {
        // Opcionalmente carrega o .env se disponível
        if (class_exists(\Dotenv\Dotenv::class)) {
            $dotenv = \Dotenv\Dotenv::createImmutable(base_path() ?? getcwd());
            $dotenv->safeLoad();
        }

        $driver = getenv('DB_CONNECTION') ?: 'sqlite';
        $host = getenv('DB_HOST') ?: 'localhost';
        $port = getenv('DB_PORT') ?: null;
        $database = getenv('DB_NAME') ?: ':memory:';
        $username = getenv('DB_USERNAME') ?: null;
        $password = getenv('DB_PASSWORD') ?: null;
        $charset = getenv('DB_CHARSET') ?: 'utf8';

        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        ];

        if ($driver === 'sqlite' && $database === ':memory:') {
            error_log('[SimpleCrud] Usando SQLite em memória como fallback. Configure seu .env para produção.');
        }

        return match ($driver) {
            'mysql' => new PDO(
                "mysql:host=$host;dbname=$database;port=$port;charset=$charset",
                $username,
                $password,
                $options
            ),
            'pgsql' => new PDO(
                "pgsql:host=$host;dbname=$database;port=$port",
                $username,
                $password,
                $options
            ),
            'sqlite' => new PDO(
                "sqlite:$database",
                null,
                null,
                $options
            ),
            'sqlsrv' => new PDO(
                "sqlsrv:Server=$host,$port;Database=$database",
                $username,
                $password,
                $options
            ),
            default => throw new RuntimeException("Driver não suportado: $driver"),
        };
    }
}
