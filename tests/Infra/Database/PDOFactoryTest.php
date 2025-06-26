<?php

declare(strict_types=1);

namespace SimplePhp\SimpleCrud\Tests\Infra\Database;

use PHPUnit\Framework\TestCase;
use SimplePhp\SimpleCrud\Infra\Database\PDOFactory;

class PDOFactoryTest extends TestCase
{
    public function testCreateFromEnvFallsBackToMemory()
    {
        putenv('DB_CONNECTION'); // limpa
        putenv('DB_DATABASE');
        $pdo = PDOFactory::createFromEnv();

        $this->assertInstanceOf(\PDO::class, $pdo);
        $this->assertEquals('sqlite', $pdo->getAttribute(\PDO::ATTR_DRIVER_NAME));
    }

    public function testCreateWithSQLite()
    {
        putenv('DB_CONNECTION=sqlite');
        putenv('DB_DATABASE=:memory:');

        $pdo = PDOFactory::createFromEnv();
        $this->assertEquals('sqlite', $pdo->getAttribute(\PDO::ATTR_DRIVER_NAME));
    }

    public function testCreateWithInvalidConnectionThrows()
    {
        putenv('DB_CONNECTION=invalid');
        putenv('DB_DATABASE=foo');
        $this->expectException(\RuntimeException::class);
        PDOFactory::createFromEnv();
    }
}
