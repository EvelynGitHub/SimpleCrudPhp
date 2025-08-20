<?php

declare(strict_types=1);

namespace SimplePhp\SimpleCrud\Tests\Facades;

use PHPUnit\Framework\TestCase;
use SimplePhp\SimpleCrud\Core\SelectBuilder;
use SimplePhp\SimpleCrud\Facades\Wrapper;
use SimplePhp\SimpleCrud\Contracts\BuilderInterface;
use SimplePhp\SimpleCrud\Contracts\ExecutableInterface;
use SimplePhp\SimpleCrud\UseCases\QueryResult;

class WrapperTest extends TestCase
{
    public function testCallForwardsToBuilderAndReturnsSelfOnFluent()
    {
        $builder = $this->createMock(SelectBuilder::class);
        $executor = $this->createMock(ExecutableInterface::class);

        $builder->expects($this->once())
            ->method('where')
            ->with('id', 1)
            ->willReturn($builder);

        $wrapper = new Wrapper($builder, $executor);
        $result = $wrapper->where('id', 1);
        $this->assertSame($wrapper, $result);
    }

    public function testCallForwardsToBuilderAndReturnsOtherValue()
    {
        $builder = $this->createMock(BuilderInterface::class);
        $executor = $this->createMock(ExecutableInterface::class);
        $builder->expects($this->once())
            ->method('getSql')
            ->willReturn('SELECT 1');
        $wrapper = new Wrapper($builder, $executor);
        $result = $wrapper->getSql();
        $this->assertEquals('SELECT 1', $result);
    }

    public function testCallThrowsOnInvalidMethod()
    {
        $builder = $this->createMock(BuilderInterface::class);
        $executor = $this->createMock(ExecutableInterface::class);
        $wrapper = new Wrapper($builder, $executor);
        $this->expectException(\BadMethodCallException::class);
        $wrapper->metodoInexistente();
    }

    public function testExecuteReturnsQueryResult()
    {
        $builder = $this->createMock(BuilderInterface::class);
        $executor = $this->createMock(ExecutableInterface::class);
        $queryResult = $this->createMock(QueryResult::class);
        $executor->expects($this->once())
            ->method('handle')
            ->with($builder)
            ->willReturn($queryResult);
        $wrapper = new Wrapper($builder, $executor);
        $result = $wrapper->execute();
        $this->assertSame($queryResult, $result);
    }
}
