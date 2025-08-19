<?php

declare(strict_types=1);

namespace SimplePhp\SimpleCrud\Facades;

use SimplePhp\SimpleCrud\UseCases\ExecuteMigrations;
use SimplePhp\SimpleCrud\UseCases\ExecuteSeeds;

class Database
{

    public const FILE_PHP = 'php';
    public const FILE_SQL = 'sql';

    private static string $migrationsPath;
    private static string $migrationsExtension = self::FILE_SQL;
    private static string $seedsPath;
    private static string $seedsExtension = self::FILE_PHP;

    /**
     * @param string $migrationsPath
     * @param self::FILE_PHP|self::FILE_SQL $migrationsExtension
     */
    public static function migration(string $migrationsPath, string $migrationsExtension): void
    {
        if (!in_array($migrationsExtension, [self::FILE_PHP, self::FILE_SQL], true)) {
            throw new \InvalidArgumentException("Extensão inválida: {$migrationsExtension}");
        }

        if (empty($migrationsPath)) {
            throw new \InvalidArgumentException("O caminho das migrations não pode ser vazio.");
        }

        self::$migrationsPath = $migrationsPath;
        self::$migrationsExtension = $migrationsExtension;
    }

    public static function getMigrationsPath(): string
    {
        return self::$migrationsPath;
    }

    public static function getMigrationsExtension(): string
    {
        return self::$migrationsExtension;
    }

    public static function seed(string $seedsPath, string $seedsExtension): void
    {
        if (!in_array($seedsExtension, [self::FILE_PHP, self::FILE_SQL], true)) {
            throw new \InvalidArgumentException("Extensão inválida: {$seedsExtension}");
        }

        if (empty($seedsPath)) {
            throw new \InvalidArgumentException("O caminho das seeds não pode ser vazio.");
        }

        self::$seedsPath = $seedsPath;
        self::$seedsExtension = $seedsExtension;
    }

    public static function getSeedsPath(): string
    {
        return self::$seedsPath;
    }

    public static function getSeedsExtension(): string
    {
        return self::$seedsExtension;
    }

    /**
     * Executa ou cria as seeds de acordo com o parâmetro `$arguments`.
     * @param string|null $arguments Se vazio, executa todas as seeds pendentes.
     *                              Se não vazio, cria uma nova seed com o nome especificado.
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     * @return string
     */
    public static function executeOrCreateSeed(?string $arguments = null): string
    {
        if (empty($arguments)) {
            $executor = new ExecuteSeeds();
            $executor->handle(
                self::$seedsPath,
                self::$seedsExtension
            );

            return "Seeds executadas com sucesso!";
        } else {
            $name = $arguments;
            $timestamp = date('Ymdhis');
            $extension = self::$seedsExtension;
            $filename = "{$timestamp}_{$name}.{$extension}";

            $path = self::$seedsPath . '/' . $filename;

            if (file_exists($path)) {
                throw new \RuntimeException("Arquivo já existe: {$filename}");
            }

            $template = self::getSeedTemplate($name);
            file_put_contents($path, $template);

            return "Seed criada: {$filename}\n";
        }
    }

    private static function getSeedTemplate(string $name): string
    {
        if (self::$seedsExtension === Database::FILE_PHP) {
            return <<<PHP_TEMPLATE
                <?php

                declare(strict_types=1);

                use SimplePhp\SimpleCrud\Facades\DB;

                function seed(): void
                {
                    // Insira aqui o código para popular a tabela {$name}
                    DB::insert('{$name}')->values([
                        'coluna1' => 'valor1',
                        'coluna2' => 'valor2'
                    ])->execute();
                }
            PHP_TEMPLATE;
        }

        return <<<SQL
            -- Insira aqui as instruções SQL para popular a tabela {$name}
            INSERT INTO {$name} (coluna1, coluna2) VALUES ('valor1', 'valor2');
        SQL;
    }

    /**
     * Executa ou cria as migrations de acordo com o parâmetro `$arguments`.
     * @param string $arguments Se vazio, executa todas as migrations pendentes.
     *                         Se não vazio, cria uma nova migration com o nome especificado.
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     * @throws \Exception
     * @throws \RuntimeException
     * @return string
     */
    public static function executeOrCreateMigrate(?string $arguments = null): string
    {
        if (empty($arguments)) {
            $executor = new ExecuteMigrations();
            $executor->handle(
                self::$migrationsPath,
                self::$migrationsExtension
            );

            return "Migrations executadas com sucesso!";
        } else {

            $name = $arguments;
            $timestamp = date('Ymdhis');
            $extension = self::$migrationsExtension;
            $filename = "{$timestamp}_{$name}.{$extension}";

            $path = self::$migrationsPath . '/' . $filename;

            if (file_exists($path)) {
                throw new \RuntimeException("Arquivo já existe: {$filename}");
            }

            $template = self::getMigrationTemplate($name);

            file_put_contents($path, $template);

            return "Migration criada: {$filename}\n";
        }
    }

    
    private static function getMigrationTemplate(string $name): string
    {
        if (self::$migrationsExtension === Database::FILE_PHP) {
            return <<<PHP_TEMPLATE
                <?php

                declare(strict_types=1);

                return "CREATE TABLE {$name} (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
                )";

                // Para rollback, implemente esta função
                function down(): string
                {
                    return "DROP TABLE IF EXISTS {$name}";
                }
            PHP_TEMPLATE;
        }

        return <<<SQL
            CREATE TABLE {$name} (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            );
        SQL;
    }


    public static function executeMigrateRollback(int $steps = 1): string
    {
        if (self::$migrationsExtension !== Database::FILE_PHP) {
            throw new \RuntimeException("Rollback só está disponível para migrations PHP");
        }

        if ($steps < 1) {
            throw new \InvalidArgumentException("Número de passos deve ser maior que 0");
        }

        $executor = new ExecuteMigrations();
        $executor->rollback($steps);

        return "Rollback de {$steps} migration(s) executado com sucesso!\n";
    }


}
