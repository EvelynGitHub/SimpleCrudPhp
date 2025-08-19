<?php

declare(strict_types=1);

namespace SimplePhp\SimpleCrud\UseCases;

use SimplePhp\SimpleCrud\Facades\DB;
use SimplePhp\SimpleCrud\Facades\Database;

class ExecuteSeeds
{
    private const TABLE_NAME = 'seeds_simplecrud';

    public function __construct()
    {
        $this->createSeedsTable();
    }

    public function handle(string $seedsPath, string $seedsExtension): void
    {
        if (!file_exists($seedsPath)) {
            throw new \RuntimeException("O diretório de seeds não existe: {$seedsPath}");
        }

        // Obtém todas as seeds já executadas de uma vez
        $executedSeeds = $this->getExecutedSeeds();
        
        // Obtém todos os arquivos de seed válidos
        $files = scandir($seedsPath);
        $pendingSeeds = array_filter($files, function($file) use ($seedsExtension, $executedSeeds) {
            return !in_array($file, ['.', '..']) && 
                   pathinfo($file, PATHINFO_EXTENSION) === $seedsExtension &&
                   !in_array($file, $executedSeeds);
        });

        // Array para armazenar as seeds executadas com sucesso
        $successfulSeeds = [];

        // Executa as seeds pendentes
        foreach ($pendingSeeds as $file) {
            try {
                $this->executeSeed($seedsPath . '/' . $file, $seedsExtension);
                $successfulSeeds[] = $file;
            } catch (\Exception $e) {
                if (!empty($successfulSeeds)) {
                    $this->markMultipleAsExecuted($successfulSeeds);
                }
                throw $e;
            }
        }

        // Marca todas as seeds bem-sucedidas como executadas de uma vez
        if (!empty($successfulSeeds)) {
            $this->markMultipleAsExecuted($successfulSeeds);
        }
    }

    private function createSeedsTable(): void
    {
        $query = "CREATE TABLE IF NOT EXISTS " . self::TABLE_NAME . " (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            seed VARCHAR(255) NOT NULL,
            executed_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )";

        DB::query($query)->execute();
    }

    private function getExecutedSeeds(): array
    {
        $result = DB::select(['seed'])
            ->from(self::TABLE_NAME)
            ->execute()
            ->fetchAll;

        return array_column($result, 'seed');
    }

    private function markMultipleAsExecuted(array $seedFiles): void
    {
        if (empty($seedFiles)) {
            return;
        }

        $values = array_map(function($file) {
            return ['seed' => $file];
        }, $seedFiles);

        DB::insert(self::TABLE_NAME)
            ->values($values)
            ->execute();
    }

    private function executeSeed(string $filePath, string $extension): void
    {
        if ($extension === Database::FILE_SQL) {
            $sql = file_get_contents($filePath);
            if (empty($sql)) {
                throw new \RuntimeException("SQL vazio no arquivo: " . basename($filePath));
            }
            DB::query($sql)->execute();
        } elseif ($extension === Database::FILE_PHP) {
            require $filePath;
            
            if (!function_exists('seed')) {
                throw new \RuntimeException(
                    "Função 'seed' não encontrada no arquivo: " . basename($filePath)
                );
            }

            seed();
        }
    }
}
