<?php

declare(strict_types=1);

namespace SimplePhp\SimpleCrud\Tests\Core;

use PHPUnit\Framework\TestCase;
use SimplePhp\SimpleCrud\Core\SelectBuilder;


class SelectBuilderTest extends TestCase
{
    /**
     * 
     * @test
     * @return void
     */
    public function simpleSelect()
    {
        $builder = new SelectBuilder();
        $builder->select(['id', 'nome'])->from('usuarios');

        $this->assertEquals('SELECT id, nome FROM usuarios', $builder->getSql());
        $this->assertEquals([], $builder->getBindings());
    }

    /**
     * 
     * @test
     * @return void
     */
    public function selectWithWhere()
    {
        $builder = new SelectBuilder();
        $builder->select('*')->from('produtos')->where('ativo = ?', [1]);

        $this->assertEquals('SELECT * FROM produtos WHERE ativo = ?', $builder->getSql());
        $this->assertEquals([1], $builder->getBindings());
    }


    /**
     * 
     * @test
     * @return void
     */
    public function selectWithLimitAndOffset()
    {
        $builder = new SelectBuilder();
        $builder->select('*')->from('pedidos')->limit(10)->offset(20);

        $this->assertEquals('SELECT * FROM pedidos LIMIT 10 OFFSET 20', $builder->getSql());
    }

    public function selectWithMultipleWhere()
    {
        $builder = new SelectBuilder();
        $builder->select('*')
            ->from('clientes')
            ->where('nome LIKE ?', ['%João%'])
            ->where('ativo = ?', [1]);

        $this->assertEquals('SELECT * FROM clientes WHERE nome LIKE ? AND ativo = ?', $builder->getSql());
        $this->assertEquals(['%João%', 1], $builder->getBindings());
    }

    public function selectWithSubSelectInWhere()
    {
        $sub = new SelectBuilder();
        $sub->select('id')->from('pedidos')->where('status = ?', ['aberto']);

        $builder = new SelectBuilder();
        $builder->select('*')
            ->from('usuarios')
            ->where("id IN ({$sub->getSql()})", $sub->getBindings());

        $this->assertEquals(
            'SELECT * FROM usuarios WHERE id IN (SELECT id FROM pedidos WHERE status = ?)',
            $builder->getSql()
        );
        $this->assertEquals(['aberto'], $builder->getBindings());
    }

    public function selectWithSubSelectInSelect()
    {
        $sub = new SelectBuilder();
        $sub->select('COUNT(*)')->from('pedidos')->where('usuario_id = usuarios.id');

        $builder = new SelectBuilder();
        $builder->select([
            'usuarios.id',
            'usuarios.nome',
            '(' . $sub->getSql() . ') AS total_pedidos'
        ])
            ->from('usuarios');

        $this->assertEquals(
            'SELECT usuarios.id, usuarios.nome, (SELECT COUNT(*) FROM pedidos WHERE usuario_id = usuarios.id) AS total_pedidos FROM usuarios',
            $builder->getSql()
        );
    }
}