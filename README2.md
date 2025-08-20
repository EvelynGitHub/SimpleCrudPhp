# Let's create the markdown content first and then write it to a file for download.

markdown_content = """
# SimpleCrudPhp  

## 📌 Sobre o Projeto  
SimpleCrudPhp é um pacote PHP puro que fornece uma interface simples para interagir com bancos de dados via PDO. Ele inclui um Query Builder, suporte a Migrations e Seeds, mantendo uma estrutura modular e flexível.  

---

## 📁 Estrutura do Projeto  

```plaintext
SimpleCrudPhp/
│── src/
│   ├── Core/
│   │   ├── Entity/
│   │   │   ├── QueryBuilder.php   # Constrói queries SQL
│   │   ├── UseCase/
│   │   │   ├── MigrationRunner.php  # Executa migrations lendo arquivos de uma pasta
│   │   │   ├── SeedRunner.php       # Executa seeds lendo arquivos de uma pasta
│   ├── Infra/
│   │   ├── Database/
│   │   │   ├── Crud.php         # Interface para construir queries
│   │   │   ├── Connection.php   # Conexão com o banco
│   │   │   ├── Migrations.php   # Chama MigrationRunner mantendo o padrão
│   │   │   ├── Seeds.php        # Chama SeedRunner mantendo o padrão
│── migrations/             # 📁 Pasta onde o usuário coloca suas migrations
│── seeds/                  # 📁 Pasta onde o usuário coloca seus seeds
│── .env                    # 🌍 Configuração do Banco de Dados
│── composer.json           # 🎵 Arquivo para registrar o pacote no Composer
│── README.md               # 📖 Documentação do Pacote
```

## 🔹 Configuração
#### 📌 Banco de Dados (.env)
Antes de usar o pacote, configure o .env com as credenciais do banco de dados:

```ini
DB_HOST=localhost
DB_NAME=meu_banco
DB_USER=root
DB_PASS=secret
```

## 🔹 Código-Fonte
#### 📌 Crud.php

```php

<?php

namespace SimpleCrudPhp\Infra\Database;

use SimpleCrudPhp\Core\Entity\QueryBuilder;
use PDO;

class Crud {
    private static ?PDO $pdo = null;
    private static ?QueryBuilder $queryBuilder = null;

    private static function init(): void {
        if (self::$pdo === null) {
            self::$pdo = Connection::getConnection();
        }
        if (self::$queryBuilder === null) {
            self::$queryBuilder = new QueryBuilder();
        }
    }

    public static function select(string $columns): self {
        self::init();
        self::$queryBuilder->select($columns);
        return new self;
    }

    public static function insert(string $table, array $data): self {
        self::init();
        self::$queryBuilder->insert($table, $data);
        return new self;
    }

    public static function update(string $table, array $data): self {
        self::init();
        self::$queryBuilder->update($table, $data);
        return new self;
    }

    public static function delete(string $table): self {
        self::init();
        self::$queryBuilder->delete($table);
        return new self;
    }

    public function where(string $condition): self {
        self::$queryBuilder->where($condition);
        return $this;
    }

    public function execute(): array|bool {
        $stmt = self::$pdo->prepare(self::$queryBuilder->getQuery());
        return $stmt->execute() ? $stmt->fetchAll(PDO::FETCH_ASSOC) : false;
    }
}
```

#### 📌 QueryBuilder.php
```php

<?php

namespace SimpleCrudPhp\Core\Entity;

class QueryBuilder {
    private string $query = '';

    public function select(string $columns): void {
        $this->query = "SELECT $columns";
    }

    public function insert(string $table, array $data): void {
        $columns = implode(', ', array_keys($data));
        $values = implode(', ', array_map(fn($v) => "'$v'", array_values($data)));
        $this->query = "INSERT INTO $table ($columns) VALUES ($values)";
    }

    public function update(string $table, array $data): void {
        $set = implode(', ', array_map(fn($k, $v) => "$k = '$v'", array_keys($data), $data));
        $this->query = "UPDATE $table SET $set";
    }

    public function delete(string $table): void {
        $this->query = "DELETE FROM $table";
    }

    public function where(string $condition): void {
        $this->query .= " WHERE $condition";
    }

    public function getQuery(): string {
        return $this->query;
    }
}
```


#### 📌 Migrations.php

```php

namespace SimpleCrudPhp\Infra\Database;

use SimpleCrudPhp\Core\UseCase\MigrationRunner;

class Migrations {
    public static function run(string $migrationPath): void {
        MigrationRunner::run($migrationPath);
    }
}
```

#### 📌 MigrationRunner.php
```php

namespace SimpleCrudPhp\Core\UseCase;

use SimpleCrudPhp\Infra\Database\Connection;
use PDO;

class MigrationRunner {
    public static function run(string $migrationPath): void {
        $pdo = Connection::getConnection();
        $files = glob($migrationPath . '/*.sql');
        
        foreach ($files as $file) {
            $sql = file_get_contents($file);
            $pdo->exec($sql);
            echo "Migration executada: " . basename($file) . PHP_EOL;
        }
    }
}
```

#### 📌 Seeds.php

```php

namespace SimpleCrudPhp\Infra\Database;

use SimpleCrudPhp\Core\UseCase\SeedRunner;

class Seeds {
    public static function run(string $seedPath): void {
        SeedRunner::run($seedPath);
    }
}

```

#### 📌 SeedRunner.php

```php

namespace SimpleCrudPhp\Core\UseCase;

use SimpleCrudPhp\Infra\Database\Connection;
use PDO;

class SeedRunner {
    public static function run(string $seedPath): void {
        $pdo = Connection::getConnection();
        $files = glob($seedPath . '/*.sql');
        
        foreach ($files as $file) {
            $sql = file_get_contents($file);
            $pdo->exec($sql);
            echo "Seed executado: " . basename($file) . PHP_EOL;
        }
    }
}
```

🛠 Como Usar no Projeto
#### 📌 Executando Queries

```php


use SimpleCrudPhp\Infra\Database\Crud;

$result = Crud::select('*')->from('users')->where('id = 1')->execute();
print_r($result);
```

#### 📌 Executando Migrations e Seeds
```php


use SimpleCrudPhp\Infra\Database\Migrations;
use SimpleCrudPhp\Infra\Database\Seeds;

Migrations::run(__DIR__ . '/migrations');
Seeds::run(__DIR__ . '/seeds');
```

#### 📌 Conclusão
✅ Chamada padronizada: Crud::, Migrations::, Seeds::
✅ Separa bem as responsabilidades entre Infra e UseCase
✅ **Suporte a Migrations e Seeds via arquivos `.sql``, mantendo a modularidade

Agora, você pode usar esse pacote em qualquer projeto PHP de forma simples! 🚀

```python


