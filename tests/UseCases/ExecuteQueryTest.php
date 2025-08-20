<?php

declare(strict_types=1);

namespace SimplePhp\SimpleCrud\Tests\UseCases;

use PDO;
use PDOStatement;
use PHPUnit\Framework\TestCase;
use SimplePhp\SimpleCrud\UseCases\ExecuteQuery;
use SimplePhp\SimpleCrud\Core\SelectBuilder;
use SimplePhp\SimpleCrud\Core\InsertBuilder;
use SimplePhp\SimpleCrud\Core\UpdateBuilder;
use SimplePhp\SimpleCrud\Core\DeleteBuilder;

class ExecuteQueryTest extends TestCase
{
    private $pdo;
    private $stmt;

    protected function setUp(): void
    {
        $this->pdo = $this->createMock(PDO::class);
        $this->stmt = $this->createMock(PDOStatement::class);
        $this->pdo->method('prepare')->willReturn($this->stmt);
    }

    public function testHandleSelectBuilderReturnsArray()
    {
        // Arrumar
        $builder = $this->createMock(SelectBuilder::class);
        $builder->method('getSql')->willReturn('SELECT * FROM usuarios');
        $builder->method('getBindings')->willReturn([]);

        $this->stmt->expects($this->once())->method('execute')->with([]);
        $this->stmt->method('fetchAll')->willReturn([
            ['id' => 1, 'nome' => 'Maria']
        ]);

        // Agir
        $useCase = new ExecuteQuery($this->pdo);
        $result = $useCase->handle($builder);

        // Afirmar
        $this->assertIsArray($result->fetchAll);
        $this->assertEquals([
            ['id' => 1, 'nome' => 'Maria']
        ], $result->fetchAll);
    }

    public function testHandleInsertBuilderReturnsRowCount()
    {
        $builder = $this->createMock(InsertBuilder::class);
        $builder->method('getSql')->willReturn('INSERT INTO usuarios (nome) VALUES (?)');
        $builder->method('getBindings')->willReturn(['Maria']);

        $this->stmt->expects($this->once())->method('execute')->with(['Maria']);
        $this->stmt->method('rowCount')->willReturn(1);


        $useCase = new ExecuteQuery($this->pdo);
        $result = $useCase->handle($builder)->rowCount;

        $this->assertIsInt($result);
        $this->assertEquals(1, $result);
    }

    public function testHandleUpdateBuilderReturnsRowCount()
    {
        $builder = $this->createMock(UpdateBuilder::class);
        $builder->method('getSql')->willReturn('UPDATE usuarios SET nome = ? WHERE id = ?');
        $builder->method('getBindings')->willReturn(['Maria', 1]);
        $this->stmt->expects($this->once())->method('execute')->with(['Maria', 1]);
        $this->stmt->method('rowCount')->willReturn(2);


        $useCase = new ExecuteQuery($this->pdo);
        $result = $useCase->handle($builder)->rowCount;

        $this->assertIsInt($result);
        $this->assertEquals(2, $result);
    }

    public function testHandleDeleteBuilderReturnsRowCount()
    {
        $builder = $this->createMock(DeleteBuilder::class);
        $builder->method('getSql')->willReturn('DELETE FROM usuarios WHERE id = ?');
        $builder->method('getBindings')->willReturn([1]);
        $this->stmt->expects($this->once())->method('execute')->with([1]);
        $this->stmt->method('rowCount')->willReturn(3);

        $useCase = new ExecuteQuery($this->pdo);
        $result = $useCase->handle($builder)->rowCount;

        $this->assertIsInt($result);
        $this->assertEquals(3, $result);
    }

    public function testHandleThrowsExceptionOnPdoError()
    {
        $builder = $this->createMock(InsertBuilder::class);
        $builder->method('getSql')->willReturn('INSERT INTO usuarios (nome) VALUES (?)');
        $builder->method('getBindings')->willReturn(['Maria']);
        $this->stmt->method('execute')->will($this->throwException(new \PDOException('Erro de execução')));
        $this->pdo->method('prepare')->willReturn($this->stmt);
        $useCase = new ExecuteQuery($this->pdo);
        $this->expectException(\PDOException::class);
        $useCase->handle($builder);
    }
}
