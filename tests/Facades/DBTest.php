<?php

declare(strict_types=1);

namespace SimplePhp\SimpleCrud\Tests\Facades;

use PDO;
use PHPUnit\Framework\TestCase;
use SimplePhp\SimpleCrud\Facades\DB;
use SimplePhp\SimpleCrud\Facades\Wrapper;
use SimplePhp\SimpleCrud\Core\SelectBuilder;
use SimplePhp\SimpleCrud\Core\InsertBuilder;
use SimplePhp\SimpleCrud\Core\UpdateBuilder;
use SimplePhp\SimpleCrud\Core\DeleteBuilder;
use SimplePhp\SimpleCrud\Core\RawQueryBuilder;
use SimplePhp\SimpleCrud\UseCases\ExecuteQuery;
use SimplePhp\SimpleCrud\UseCases\QueryResult;

class DBTest extends TestCase
{
    private $pdo;

    protected function setUp(): void
    {
        $this->pdo = $this->createMock(PDO::class);
        DB::connect($this->pdo);
    }

    public function testSelectReturnsWrapper()
    {
        $wrapper = DB::select(['id', 'nome']);
        $this->assertInstanceOf(Wrapper::class, $wrapper);
    }

    public function testInsertReturnsWrapper()
    {
        $wrapper = DB::insert('produtos');
        $this->assertInstanceOf(Wrapper::class, $wrapper);
    }

    public function testUpdateReturnsWrapper()
    {
        $wrapper = DB::update('produtos');
        $this->assertInstanceOf(Wrapper::class, $wrapper);
    }

    public function testDeleteReturnsWrapper()
    {
        $wrapper = DB::delete('produtos');
        $this->assertInstanceOf(Wrapper::class, $wrapper);
    }

    public function testQueryReturnsWrapper()
    {
        $wrapper = DB::query('SELECT * FROM produtos WHERE id = ?', [1]);
        $this->assertInstanceOf(Wrapper::class, $wrapper);
    }

    public function testSelectBuilderIsConfigured()
    {
        $wrapper = DB::select(['id', 'nome']);

        $reflection = new \ReflectionClass($wrapper);
        // $property equivale ao valor de $builder da classe Wrapper = "private BuilderInterface $builder"
        $property = $reflection->getProperty('builder');
        // setAccessible(true) permite acessar propriedades privadas
        $property->setAccessible(true);
        // getValue($wrapper) obtém o valor da propriedade $builder do objeto $wrapper
        $builder = $property->getValue($wrapper);

        $this->assertInstanceOf(SelectBuilder::class, $builder);
    }

    public function testInsertBuilderIsConfigured()
    {
        $wrapper = DB::insert('produtos');
        $reflection = new \ReflectionClass($wrapper);
        $property = $reflection->getProperty('builder');
        $property->setAccessible(true);
        $builder = $property->getValue($wrapper);
        $this->assertInstanceOf(InsertBuilder::class, $builder);
    }

    public function testUpdateBuilderIsConfigured()
    {
        $wrapper = DB::update('produtos');
        $reflection = new \ReflectionClass($wrapper);
        $property = $reflection->getProperty('builder');
        $property->setAccessible(true);
        $builder = $property->getValue($wrapper);
        $this->assertInstanceOf(UpdateBuilder::class, $builder);
    }

    public function testDeleteBuilderIsConfigured()
    {
        $wrapper = DB::delete('produtos');
        $reflection = new \ReflectionClass($wrapper);
        $property = $reflection->getProperty('builder');
        $property->setAccessible(true);
        $builder = $property->getValue($wrapper);
        $this->assertInstanceOf(DeleteBuilder::class, $builder);
    }

    public function testQueryBuilderIsConfigured()
    {
        $wrapper = DB::query('SELECT * FROM produtos WHERE id = ?', [1]);
        $reflection = new \ReflectionClass($wrapper);
        $property = $reflection->getProperty('builder');
        $property->setAccessible(true);
        $builder = $property->getValue($wrapper);
        $this->assertInstanceOf(RawQueryBuilder::class, $builder);
    }

    public function testSelectChainedBuildsCorrectSql()
    {
        $sql = DB::select(['id', 'nome'])
            ->from('pessoa')
            ->where('habilitado', true)
            ->getSql();
        $bindings = DB::select(['id', 'nome'])
            ->from('pessoa')
            ->where('habilitado', true)
            ->getBindings();
        $this->assertEquals('SELECT id, nome FROM pessoa WHERE habilitado = ?', $sql);
        $this->assertEquals([true], $bindings);
    }

    /**
     * O caso do insert é um pouco diferente, pois o método `values` retorna void,
     * já que nada pode ser chamado depois dele no Insert. Isso significa que
     * o método `getSql` e `getBindings` devem ser chamados no objeto retornado pelo método `DD::insert()`. 
     * Por exemplo: O seguinte código:
     * @return void
     */
    public function testInsertChainedBuildsCorrectSql()
    {
        $db = DB::insert('produtos');
        $db->values(['id' => 1, 'nome' => 'Teclado']);

        $sql = $db->getSql();
        $bindings = $db->getBindings();

        $this->assertEquals('INSERT INTO produtos (id, nome) VALUES (?, ?)', $sql);
        $this->assertEquals([1, 'Teclado'], $bindings);
    }

    public function testUpdateChainedBuildsCorrectSql()
    {
        $sql = DB::update('usuarios')
            ->set('nome', 'Novo Nome')
            ->where('id', 5)
            ->getSql();
        $bindings = DB::update('usuarios')
            ->set('nome', 'Novo Nome')
            ->where('id', 5)
            ->getBindings();
        $this->assertEquals('UPDATE usuarios SET nome = ? WHERE id = ?', $sql);
        $this->assertEquals(['Novo Nome', 5], $bindings);
    }

    public function testDeleteChainedBuildsCorrectSql()
    {
        $sql = DB::delete('usuarios')
            ->where('ativo', false)
            ->getSql();
        $bindings = DB::delete('usuarios')
            ->where('ativo', false)
            ->getBindings();
        $this->assertEquals('DELETE FROM usuarios WHERE ativo = ?', $sql);
        $this->assertEquals([false], $bindings);
    }

    public function testSelectExecuteReturnsResult()
    {
        $mockResult = new QueryResult([['id' => 1, 'nome' => 'Maria']]);
        $executor = $this->createMock(ExecuteQuery::class);
        $executor->expects($this->once())
            ->method('handle')
            ->willReturn($mockResult);
        $builder = $this->createMock(SelectBuilder::class);
        $wrapper = new Wrapper($builder, $executor);
        $result = $wrapper->execute();
        $this->assertEquals($mockResult, $result);
    }

    public function testInsertExecuteReturnsResult()
    {
        $mockResult = new QueryResult([[1]]);
        $executor = $this->createMock(ExecuteQuery::class);
        $executor->expects($this->once())
            ->method('handle')
            ->willReturn($mockResult);
        $builder = $this->createMock(InsertBuilder::class);
        $wrapper = new Wrapper($builder, $executor);
        $result = $wrapper->execute();
        $this->assertEquals($mockResult, $result);
    }

    public function testUpdateExecuteReturnsResult()
    {
        $mockResult = new QueryResult([[2]]);
        $executor = $this->createMock(ExecuteQuery::class);
        $executor->expects($this->once())
            ->method('handle')
            ->willReturn($mockResult);
        $builder = $this->createMock(UpdateBuilder::class);
        $wrapper = new Wrapper($builder, $executor);
        $result = $wrapper->execute();
        $this->assertEquals($mockResult, $result);
    }

    public function testDeleteExecuteReturnsResult()
    {
        $mockResult = new QueryResult([[3]]);
        $executor = $this->createMock(ExecuteQuery::class);
        $executor->expects($this->once())
            ->method('handle')
            ->willReturn($mockResult);
        $builder = $this->createMock(DeleteBuilder::class);
        $wrapper = new Wrapper($builder, $executor);
        $result = $wrapper->execute();
        $this->assertEquals($mockResult, $result);
    }
}
