<?php

declare(strict_types=1);

namespace SimplePhp\SimpleCrud\Tests\Core;

use PHPUnit\Framework\TestCase;
use SimplePhp\SimpleCrud\Core\SelectBuilder;


class SelectBuilderTest extends TestCase
{
    public function testSimpleSelect()
    {
        $builder = new SelectBuilder();
        $builder->select(['id', 'nome'])->from('usuarios');

        $this->assertEquals('SELECT id, nome FROM usuarios', $builder->getSql());
        $this->assertEquals([], $builder->getBindings());
    }

    public function testSelectWithWhere()
    {
        $builder = new SelectBuilder();
        $builder->select('*')->from('produtos')->where('ativo', 1);

        $this->assertEquals('SELECT * FROM produtos WHERE ativo = ?', $builder->getSql());
        $this->assertEquals([1], $builder->getBindings());
    }


    public function testSelectWithLimitAndOffset()
    {
        $builder = new SelectBuilder();
        $builder->select('*')->from('pedidos')->limit(10)->offset(20);

        $this->assertEquals('SELECT * FROM pedidos LIMIT 10 OFFSET 20', $builder->getSql());
    }

    public function testSelectWithMultipleWhere()
    {
        $builder = new SelectBuilder();
        $builder->select('*')
            ->from('clientes')
            ->where('nome', 'LIKE', '%João%')
            ->where('ativo', 1);

        $this->assertEquals('SELECT * FROM clientes WHERE nome LIKE ? AND ativo = ?', $builder->getSql());
        $this->assertEquals(['%João%', 1], $builder->getBindings());
    }

    public function testSelectWithSubSelectInWhere()
    {
        $sub = new SelectBuilder();
        $sub->select('id')->from('pedidos')->where('status', 'aberto');

        $builder = new SelectBuilder();
        $builder->select('*')
            ->from('usuarios')
            ->whereIn('id', $sub);


        $builder2 = new SelectBuilder();
        $builder2->select('*')
            ->from('usuarios')
            ->where("id", 'IN', $sub);

        $this->assertEquals(
            'SELECT * FROM usuarios WHERE id IN (SELECT id FROM pedidos WHERE status = ?)',
            $builder->getSql()
        );
        $this->assertEquals(['aberto'], $builder->getBindings());

        $this->assertEquals(
            'SELECT * FROM usuarios WHERE id IN (SELECT id FROM pedidos WHERE status = ?)',
            $builder2->getSql()
        );
        $this->assertEquals(['aberto'], $builder2->getBindings());
    }

    public function testSelectWithSubSelectInSelect()
    {
        $sub = new SelectBuilder();
        $sub->aliasSubQuery('total_pedidos');
        $sub->select('COUNT(*)')->from('pedidos')->where('usuario_id = usuarios.id');

        $builder = new SelectBuilder();
        $builder->select([
            'usuarios.id',
            'usuarios.nome',
            $sub
        ])
            ->from('usuarios');

        $this->assertEquals(
            'SELECT usuarios.id, usuarios.nome, (SELECT COUNT(*) FROM pedidos WHERE usuario_id = usuarios.id) AS total_pedidos FROM usuarios',
            $builder->getSql()
        );
    }


    public function testSelectWithWhereOr(): void
    {
        $builder = new SelectBuilder();
        $builder->select('usuarios.id', 'usuarios.nome')
            ->from('usuarios')
            ->join('clientes', 'usuarios.id = clientes.usuario_id AND clientes.ativo = true')
            ->where('usuarios.ativo', '=', 1)
            ->where('usuarios.habilitado', '=', 2)
            ->orWhere('clientes.habilitado', '=', 3);

        $this->assertEquals(
            $this->normalizeSql(
                'SELECT usuarios.id, usuarios.nome 
                    FROM usuarios 
                    INNER JOIN clientes ON usuarios.id = clientes.usuario_id AND clientes.ativo = true
                    WHERE usuarios.ativo = ?
                    AND usuarios.habilitado = ?
                    OR clientes.habilitado = ?'
            ),
            $builder->getSql()
        );
        $this->assertEquals([1, 2, 3], $builder->getBindings());

        $builder2 = new SelectBuilder();
        $builder2->select('usuarios.id', 'usuarios.nome')
            ->from('usuarios')
            ->join('clientes', 'usuarios.id = clientes.usuario_id AND clientes.ativo = true')
            ->where('usuarios.ativo', '=', 4)
            ->orWhere(function ($query) {
                $query->where('usuarios.habilitado', '=', 5)
                    ->orWhere('clientes.habilitado', '=', 6);

            });

        $this->assertEquals(
            $this->normalizeSql(
                'SELECT usuarios.id, usuarios.nome 
                    FROM usuarios 
                    INNER JOIN clientes ON usuarios.id = clientes.usuario_id AND clientes.ativo = true
                    WHERE usuarios.ativo = ?
                    OR (usuarios.habilitado = ? OR clientes.habilitado = ?)'
            ),
            $builder2->getSql()
        );
        $this->assertEquals([4, 5, 6], $builder2->getBindings());

        $builder3 = new SelectBuilder();
        $builder3->select('usuarios.id', 'usuarios.nome')
            ->from('usuarios')
            ->join('clientes', 'usuarios.id = clientes.usuario_id AND clientes.ativo = true')
            ->where('usuarios.ativo', '=', true)
            ->where(function ($query) {
                $query->where('usuarios.habilitado', '=', true)
                    ->orWhere('clientes.habilitado', '=', true);

            });

        $this->assertEquals(
            $this->normalizeSql(
                'SELECT usuarios.id, usuarios.nome 
                    FROM usuarios 
                    INNER JOIN clientes ON usuarios.id = clientes.usuario_id AND clientes.ativo = true
                    WHERE usuarios.ativo = ?
                    AND (usuarios.habilitado = ? OR clientes.habilitado = ?)'
            ),
            $builder3->getSql()
        );

        $this->assertEquals([true, true, true], $builder3->getBindings());
    }


    public function testSelectWithWhereTypeArraySimpleWithAnd(): void
    {
        $builder = new SelectBuilder();
        $builder->select('usuarios.id', 'usuarios.nome')
            ->from('usuarios')
            ->join('clientes', 'usuarios.id = clientes.usuario_id AND clientes.ativo = true')
            ->where([
                'usuarios.ativo' => 1,
                'usuarios.habilitado' => 2,
                'clientes.habilitado' => 3
            ]);

        $testeQuantidade = 'clientes_organizacoes = produto_id AND organizacoes = true AND organizacao = true AND organizacao = true';

        $this->assertEquals(
            $this->normalizeSql(
                'SELECT usuarios.id, usuarios.nome 
                    FROM usuarios 
                    INNER JOIN clientes ON usuarios.id = clientes.usuario_id AND clientes.ativo = true
                    WHERE usuarios.ativo = ?
                    AND usuarios.habilitado = ?
                    AND clientes.habilitado = ?'
            ),
            $builder->getSql()
        );
        $this->assertEquals([1, 2, 3], $builder->getBindings());
    }


    public function testSelectWithWhereTypeArraySimpleWithAndOr(): void
    {
        $builder = new SelectBuilder();
        $builder->select('usuarios.id', 'usuarios.nome')
            ->from('usuarios')
            ->join('clientes', 'usuarios.id = clientes.usuario_id AND clientes.ativo = true')
            ->where([
                'usuarios.ativo' => 1,
                'OR' => [
                    'usuarios.habilitado' => 2,
                    'clientes.habilitado' => 3
                ]
            ]);

        $this->assertEquals(
            $this->normalizeSql(
                'SELECT usuarios.id, usuarios.nome 
                    FROM usuarios 
                    INNER JOIN clientes ON usuarios.id = clientes.usuario_id AND clientes.ativo = true
                    WHERE usuarios.ativo = ?
                    AND (usuarios.habilitado = ?
                    OR clientes.habilitado = ?)'
            ),
            $builder->getSql()
        );
        $this->assertEquals([1, 2, 3], $builder->getBindings());
    }

    public function testSelectWithWhereTypeArray(): void
    {
        $builder = new SelectBuilder();
        $builder->select('usuarios.id', 'usuarios.nome')
            ->from('usuarios')
            ->join('clientes', 'usuarios.id = clientes.usuario_id AND clientes.ativo = true')
            ->join('produtos_favoritos', 'produtos_favoritos.id = clientes.favoritados_id')
            ->join('produtos', 'produtos.id = produtos_favoritos.produto_id')
            ->where([
                'usuarios.ativo' => 1,
                'OR' => [
                    'usuarios.habilitado' => 2,
                    'clientes.habilitado' => 3
                ],
                'produtos_favoritos.id' => [
                    'IN' => [
                        12,
                        222,
                        333
                    ]
                ]
            ])
            ->where('produtos_favoritos.id IS NOT NULL');

        $this->assertEquals(
            $this->normalizeSql(
                'SELECT usuarios.id, usuarios.nome 
                    FROM usuarios 
                    INNER JOIN clientes ON usuarios.id = clientes.usuario_id AND clientes.ativo = true
                    INNER JOIN produtos_favoritos ON produtos_favoritos.id = clientes.favoritados_id
                    INNER JOIN produtos ON produtos.id = produtos_favoritos.produto_id
                    WHERE usuarios.ativo = ?
                    AND (usuarios.habilitado = ?
                    OR clientes.habilitado = ?)
                    AND (produtos_favoritos.id IN (?, ?, ?)) 
                    AND produtos_favoritos.id IS NOT NULL'
            ),
            $builder->getSql()
        );
        $this->assertEquals([1, 2, 3, 12, 222, 333], $builder->getBindings());
    }



    protected function normalizeSql(string $sql): string
    {
        // 1. Substituir todas as quebras de linha por um espaço
        $sql = str_replace(["\n", "\r"], ' ', $sql);

        // 2. Remover múltiplos espaços em branco (incluindo tabulações) por um único espaço
        $sql = preg_replace('/\s+/', ' ', $sql);

        // 3. Remover espaços em branco no início e no fim da string
        $sql = trim($sql);

        return $sql;
    }
}