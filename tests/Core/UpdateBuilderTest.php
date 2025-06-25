<?php

declare(strict_types=1);

namespace SimplePhp\SimpleCrud\Tests\Core;

use PHPUnit\Framework\TestCase;
use SimplePhp\SimpleCrud\Core\UpdateBuilder;
use SimplePhp\SimpleCrud\Core\SelectBuilder;

class UpdateBuilderTest extends TestCase
{
    public function testUpdateSimpleWithWhere()
    {
        $builder = new UpdateBuilder();
        $builder->table('produtos')->set('preco', 25.50)->where('id', '=', 1);
        $this->assertEquals('UPDATE produtos SET preco = ? WHERE id = ?', $builder->getSql());
        $this->assertEquals([25.50, 1], $builder->getBindings());
    }

    public function testUpdateMultipleColumnsWithWhere()
    {
        $builder = new UpdateBuilder();
        $builder->table('usuarios')->set([
            'nome' => 'Novo Nome',
            'email' => 'novo@email.com'
        ])->where('id', '=', 5);
        $this->assertEquals('UPDATE usuarios SET nome = ?, email = ? WHERE id = ?', $builder->getSql());
        $this->assertEquals(['Novo Nome', 'novo@email.com', 5], $builder->getBindings());
    }

    public function testUpdateAllRows()
    {
        $builder = new UpdateBuilder();
        $builder->table('produtos')->set('estoque', 0);
        $this->assertEquals('UPDATE produtos SET estoque = ?', $builder->getSql());
        $this->assertEquals([0], $builder->getBindings());
    }

    public function testUpdateWithWhereLike()
    {
        $builder = new UpdateBuilder();
        $builder->table('clientes')->set('status', 'inativo')->where('nome', 'LIKE', 'João%');
        $this->assertEquals('UPDATE clientes SET status = ? WHERE nome LIKE ?', $builder->getSql());
        $this->assertEquals(['inativo', 'João%'], $builder->getBindings());
    }

    public function testUpdateWithWhereIn()
    {
        $builder = new UpdateBuilder();
        $builder->table('pedidos')->set('pago', true)->whereIn('id', [101, 105, 107]);
        $this->assertEquals('UPDATE pedidos SET pago = ? WHERE id IN (?, ?, ?)', $builder->getSql());
        $this->assertEquals([true, 101, 105, 107], $builder->getBindings());
    }

    public function testUpdateWithCaseStatement()
    {
        $builder = new UpdateBuilder();
        $builder->table('tarefas')
            ->set('prioridade', new \SimplePhp\SimpleCrud\Core\RawQueryBuilder(
                "CASE WHEN data_vencimento < CURDATE() THEN 'Alta' ELSE 'Normal' END"
            ));

        $this->assertEquals(
            "UPDATE tarefas SET prioridade = (CASE WHEN data_vencimento < CURDATE() THEN 'Alta' ELSE 'Normal' END)",
            $builder->getSql()
        );
        $this->assertEquals([], $builder->getBindings());
    }

    public function testUpdateWithMathFunction()
    {
        $builder = new UpdateBuilder();
        $builder->table('produtos')->set('preco', new \SimplePhp\SimpleCrud\Core\RawQueryBuilder('preco * 1.10'))
            ->where('categoria', '=', 'Eletrônicos');
        $this->assertEquals('UPDATE produtos SET preco = (preco * 1.10) WHERE categoria = ?', $builder->getSql());
        $this->assertEquals(['Eletrônicos'], $builder->getBindings());
    }

    public function testUpdateWithJoinThrowsException()
    {
        $builder = new UpdateBuilder();
        $builder->table('pedidos')
            ->join('clientes c', 'pedidos.cliente_id = c.id')
            ->set('status', 'cancelado')
            ->where('c.status', '=', 'banido');

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage(
            'UPDATE com JOINs, LIMIT ou OFFSET não é suportado nativamente por todos os bancos.
            Use subquery no WHERE ou query livre com query().
            Não esqueça de usar os bindings corretamente.'
        );
        $builder->getSql();
    }

    public function testUpdateWithSubqueryInSet()
    {
        $sub = new SelectBuilder();
        $sub->select('MAX(data_venda)')->from('vendas')->where('produto_id = produtos.id');

        $builder = new UpdateBuilder();
        $builder->table('produtos')
            ->set('ultima_venda', $sub)
            ->where('id', '=', 20);

        $this->assertEquals(
            'UPDATE produtos SET ultima_venda = (SELECT MAX(data_venda) FROM vendas WHERE produto_id = produtos.id) WHERE id = ?',
            $builder->getSql()
        );
        $this->assertEquals([20], $builder->getBindings());
    }

    public function testUpdateWithNull()
    {
        $builder = new UpdateBuilder();
        $builder->table('usuarios')->set('telefone', null)->where('id', '=', 15);
        $this->assertEquals('UPDATE usuarios SET telefone = ? WHERE id = ?', $builder->getSql());
        $this->assertEquals([null, 15], $builder->getBindings());
    }

    public function testUpdateWithEmptyString()
    {
        $builder = new UpdateBuilder();
        $builder->table('produtos')
            ->set('descricao', '')
            ->where('descricao IS null');

        $this->assertEquals("UPDATE produtos SET descricao = ? WHERE descricao IS null", $builder->getSql());
        $this->assertEquals([''], $builder->getBindings());
    }

    public function testUpdateWithDateFunction()
    {
        $builder = new UpdateBuilder();
        $builder->table('sessoes')
            ->set('ultimo_acesso', new \SimplePhp\SimpleCrud\Core\RawQueryBuilder('NOW()'))
            ->where('usuario_id', '=', 30);

        $this->assertEquals('UPDATE sessoes SET ultimo_acesso = (NOW()) WHERE usuario_id = ?', $builder->getSql());
        $this->assertEquals([30], $builder->getBindings());
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
