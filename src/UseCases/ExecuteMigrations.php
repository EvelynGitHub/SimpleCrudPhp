<?php

declare(strict_types=1);

namespace SimplePhp\SimpleCrud\UseCases;

use SimplePhp\SimpleCrud\Facades\DB;
use SimplePhp\SimpleCrud\Facades\Database;

class ExecuteMigrations
{
    private const TABLE_NAME = 'migrations_simplecrud';

    public function __construct()
    {
        $this->createMigrationsTable();
    }

    public function handle(string $migrationsPath, string $migrationsExtension): void
    {
        if (!file_exists($migrationsPath)) {
            throw new \RuntimeException("O diretório de migrations não existe: {$migrationsPath}");
        }

        // Obtém todas as migrations já executadas de uma vez
        $executedMigrations = $this->getExecutedMigrations();
        
        // Obtém todos os arquivos de migration válidos
        $files = scandir($migrationsPath);
        $pendingMigrations = array_filter($files, function($file) use ($migrationsExtension, $executedMigrations) {
            return !in_array($file, ['.', '..']) && 
                   pathinfo($file, PATHINFO_EXTENSION) === $migrationsExtension &&
                   !in_array($file, $executedMigrations);
        });

        // Array para armazenar as migrations executadas com sucesso
        $successfulMigrations = [];

        // Executa as migrations pendentes
        foreach ($pendingMigrations as $file) {
            try {
                $this->executeMigration($migrationsPath . '/' . $file, $migrationsExtension);
                $successfulMigrations[] = $file;
            } catch (\Exception $e) {
                if (!empty($successfulMigrations)) {
                    $this->markMultipleAsExecuted($successfulMigrations);
                }
                throw $e;
            }
        }

        // Marca todas as migrations bem-sucedidas como executadas de uma vez
        if (!empty($successfulMigrations)) {
            $this->markMultipleAsExecuted($successfulMigrations);
        }
    }

    private function createMigrationsTable(): void
    {
        $query = "CREATE TABLE IF NOT EXISTS " . self::TABLE_NAME . " (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            migration VARCHAR(255) NOT NULL,
            executed_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )";

        DB::query($query)->execute();
    }

    private function getExecutedMigrations(): array
    {
        $result = DB::select(['migration'])
            ->from(self::TABLE_NAME)
            ->execute()
            ->fetchAll;

        return array_column($result, 'migration');
    }

    private function markMultipleAsExecuted(array $migrationFiles): void
    {
        if (empty($migrationFiles)) {
            return;
        }

        $values = array_map(function($file) {
            return ['migration' => $file];
        }, $migrationFiles);

        DB::insert(self::TABLE_NAME)
            ->values($values)
            ->execute();
    }

    private function executeMigration(string $filePath, string $extension): void
    {
        $sql = '';

        if ($extension === Database::FILE_SQL) {
            $sql = file_get_contents($filePath);
        } elseif ($extension === Database::FILE_PHP) {
            $sql = require $filePath;

            if (!is_string($sql)) {
                throw new \RuntimeException(
                    "O arquivo de migration PHP deve retornar uma string SQL: " . basename($filePath)
                );
            }
        }

        if (empty($sql)) {
            throw new \RuntimeException("SQL vazio no arquivo: " . basename($filePath));
        }

        DB::query($sql)->execute();
    }

    private function markAsExecuted(string $migrationFile): void
    {
        DB::insert(self::TABLE_NAME)
            ->values(['migration' => $migrationFile])
            ->execute();
    }

    public function rollback(int $steps): void
    {
        if (Database::getMigrationsExtension() !== Database::FILE_PHP) {
            throw new \RuntimeException("Rollback só está disponível para migrations PHP");
        }

        $executed = DB::select(['migration'])
            ->from(self::TABLE_NAME)
            ->orderBy('id DESC')
            ->limit($steps)
            ->execute()
            ->fetchAll;

        foreach ($executed as $migration) {
            $this->rollbackMigration(
                Database::getMigrationsPath() . '/' . $migration['migration']
            );
            $this->removeMigrationRecord($migration['migration']);
        }
    }

    private function rollbackMigration(string $filePath): void
    {
        require_once $filePath;

        if (!function_exists('down')) {
            throw new \RuntimeException(
                "Função 'down' não encontrada no arquivo: " . basename($filePath)
            );
        }

        $sql = down();

        if (!is_string($sql)) {
            throw new \RuntimeException(
                "A função 'down' deve retornar uma string SQL: " . basename($filePath)
            );
        }

        if (empty($sql)) {
            throw new \RuntimeException("SQL vazio no rollback do arquivo: " . basename($filePath));
        }

        DB::query($sql)->execute();
    }

    private function removeMigrationRecord(string $migrationFile): void
    {
        DB::delete(self::TABLE_NAME)
            ->where('migration = ?', [$migrationFile])
            ->execute();
    }
}
