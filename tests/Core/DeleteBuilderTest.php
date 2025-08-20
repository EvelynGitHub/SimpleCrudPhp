<?php

declare(strict_types=1);

namespace SimplePhp\SimpleCrud\Tests\Core;

use PHPUnit\Framework\TestCase;
use SimplePhp\SimpleCrud\Core\DeleteBuilder;
use SimplePhp\SimpleCrud\Core\SelectBuilder;

class DeleteBuilderTest extends TestCase
{
    public function testDeleteSimple()
    {
        $builder = new DeleteBuilder();
        $builder->from('logs');
        $this->assertEquals('DELETE FROM logs', $builder->getSql());
        $this->assertEquals([], $builder->getBindings());
    }

    public function testDeleteWithWhere()
    {
        $builder = new DeleteBuilder();
        $builder->from('usuarios')->where('status', '=', 'inativo');
        $this->assertEquals('DELETE FROM usuarios WHERE status = ?', $builder->getSql());
        $this->assertEquals(['inativo'], $builder->getBindings());
    }

    public function testDeleteWithWhereSubquery()
    {
        $sub = new SelectBuilder();
        $sub->select('id')->from('clientes')
            ->where('cidade', '=', 'São Paulo');

        $builder = new DeleteBuilder();
        $builder->from('pedidos')
            ->whereIn('id_cliente', $sub);

        $this->assertEquals(
            'DELETE FROM pedidos WHERE id_cliente IN (SELECT id FROM clientes WHERE cidade = ?)',
            $builder->getSql()
        );
        $this->assertEquals(['São Paulo'], $builder->getBindings());
    }

    public function testDeleteWithJoin()
    {
        $builder = new DeleteBuilder();
        $builder->from('tabela1 t1')
            ->join('tabela2 t2', 't1.id = t2.tabela1_id')
            ->where('t2.data_expiracao', '<', '2025-06-23');

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('DELETE com JOINs, LIMIT ou OFFSET não é suportado.
                Use uma subquery no WHERE ou query livre query().
                Não esqueça de usar os bindings corretamente.');

        $this->assertEquals(
            'DELETE t1 FROM tabela1 t1 INNER JOIN tabela2 t2 ON t1.id = t2.tabela1_id WHERE t2.data_expiracao < ?',
            $builder->getSql()
        );
        $this->assertEquals(['2025-06-23'], $builder->getBindings());
    }

    public function testDeleteWithLimit()
    {
        $builder = new DeleteBuilder();
        $builder->from('logs')->orderBy('data', 'ASC')->limit(10000);


        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('DELETE com JOINs, LIMIT ou OFFSET não é suportado.
                Use uma subquery no WHERE ou query livre query().
                Não esqueça de usar os bindings corretamente.');

        $this->assertEquals('DELETE FROM logs ORDER BY data ASC LIMIT 10000', $builder->getSql());
    }

    public function testDeleteWithLimitAndOffset()
    {
        $builder = new DeleteBuilder();
        $builder->from('temp_data')->orderBy('id')->limit(5000)->offset(10000);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('DELETE com JOINs, LIMIT ou OFFSET não é suportado.
                Use uma subquery no WHERE ou query livre query().
                Não esqueça de usar os bindings corretamente.');

        $this->assertEquals('DELETE FROM temp_data ORDER BY id LIMIT 5000 OFFSET 10000', $builder->getSql());
    }
}
