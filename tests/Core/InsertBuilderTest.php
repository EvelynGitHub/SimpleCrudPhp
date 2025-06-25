<?php

declare(strict_types=1);

namespace SimplePhp\SimpleCrud\Tests\Core;

use PHPUnit\Framework\TestCase;
use SimplePhp\SimpleCrud\Core\InsertBuilder;
use SimplePhp\SimpleCrud\Core\RawQueryBuilder;
use SimplePhp\SimpleCrud\Core\SelectBuilder;

class InsertBuilderTest extends TestCase
{
    public function testInsertSimpleAllColumns()
    {
        $builder = new InsertBuilder();
        $builder->table('produtos')->values([
            'id' => 1,
            'nome' => 'Teclado',
            'preco' => 150.00,
            'estoque' => 100
        ]);
        $this->assertEquals('INSERT INTO produtos (id, nome, preco, estoque) VALUES (?, ?, ?, ?)', $builder->getSql());
        $this->assertEquals([1, 'Teclado', 150.00, 100], $builder->getBindings());
    }

    public function testInsertSimpleSelectedColumns()
    {
        $builder = new InsertBuilder();
        $builder->table('usuarios')->values([
            'nome' => 'Maria Silva',
            'email' => 'maria@exemplo.com'
        ]);
        $this->assertEquals('INSERT INTO usuarios (nome, email) VALUES (?, ?)', $builder->getSql());
        $this->assertEquals(['Maria Silva', 'maria@exemplo.com'], $builder->getBindings());
    }

    public function testInsertWithNullExplicit()
    {
        $builder = new InsertBuilder();
        $builder->table('pedidos')->values([
            'id' => 10,
            'cliente_id' => 5,
            'data_pedido' => '2025-06-25',
            'valor_total' => 200.00,
            'observacoes' => null
        ]);
        $this->assertEquals('INSERT INTO pedidos (id, cliente_id, data_pedido, valor_total, observacoes) VALUES (?, ?, ?, ?, ?)', $builder->getSql());
        $this->assertEquals([10, 5, '2025-06-25', 200.00, null], $builder->getBindings());
    }

    public function testInsertWithDefaultValues()
    {
        $builder = new InsertBuilder();
        $builder->table('tarefas')->values([
            'titulo' => 'Comprar leite',
            'descricao' => 'Não esquecer o desnatado'
            // status não informado, assume default do banco
        ]);
        $this->assertEquals('INSERT INTO tarefas (titulo, descricao) VALUES (?, ?)', $builder->getSql());
        $this->assertEquals(['Comprar leite', 'Não esquecer o desnatado'], $builder->getBindings());
    }

    public function testInsertBatchMultipleRows()
    {
        $builder = new InsertBuilder();
        $builder->table('categorias')->values([
            ['id' => 1, 'nome' => 'Eletrônicos'],
            ['id' => 2, 'nome' => 'Livros'],
            ['id' => 3, 'nome' => 'Roupas']
        ]);
        $this->assertEquals('INSERT INTO categorias (id, nome) VALUES (?, ?), (?, ?), (?, ?)', $builder->getSql());
        $this->assertEquals([1, 'Eletrônicos', 2, 'Livros', 3, 'Roupas'], $builder->getBindings());
    }

    /**
     * 
     * @ignore Não se aplica ao caso do INSERT
     * @return void
     */
    public function InsertWithSqlFunction()
    {
        $builder = new InsertBuilder();
        $builder->table('logs')->values([
            'mensagem' => 'Usuário logado',
            'data_hora' => new RawQueryBuilder('NOW()')
        ]);
        $this->assertEquals('INSERT INTO logs (mensagem, data_hora) VALUES (?, (NOW()))', $builder->getSql());
        $this->assertEquals(['Usuário logado'], $builder->getBindings());
    }

    public function testInsertWithSubquery()
    {
        $sub = new SelectBuilder();
        $sub->select('id', 'nome', 'email')
            ->from('usuarios')
            ->where('status', '=', 'inativo');

        $builder = new InsertBuilder();
        $builder->table('clientes_inativos')
            ->valuesWhitSelect(['id', 'nome', 'email'], $sub);

        $this->assertEquals(
            'INSERT INTO clientes_inativos (id, nome, email) SELECT id, nome, email FROM usuarios WHERE status = ?',
            $builder->getSql()
        );
        $this->assertEquals(['inativo'], $builder->getBindings());
    }

    /**
     * Não se aplica
     */
    public function InsertWithCalculatedValue()
    {
        $builder = new InsertBuilder();
        $builder->table('vendas')->values([
            'produto_id' => 123,
            'quantidade' => 5,
            'preco_total' => new RawQueryBuilder('5 * 25.00')
        ]);
        $this->assertEquals('INSERT INTO vendas (produto_id, quantidade, preco_total) VALUES (?, ?, (5 * 25.00))', $builder->getSql());
        $this->assertEquals([123, 5], $builder->getBindings());
    }

    public function testInsertWithSpecialCharacters()
    {
        $builder = new InsertBuilder();
        $builder->table('comentarios')->values([
            'texto' => 'Isso é um "ótimo" produto!'
        ]);
        $this->assertEquals('INSERT INTO comentarios (texto) VALUES (?)', $builder->getSql());
        $this->assertEquals(['Isso é um "ótimo" produto!'], $builder->getBindings());
    }

    public function testInsertWithInvalidArrayThrows()
    {
        $builder = new InsertBuilder();
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Número de valores não bate com as colunas.');
        $builder->table('produtos')->values([
            ['id' => 1, 'nome' => 'Teclado'],
            ['id' => 2] // faltando coluna 'nome'
        ]);
    }

    public function testInsertWithEmptyArrayThrows()
    {
        $builder = new InsertBuilder();
        $this->expectException(\RuntimeException::class);
        $builder->table('produtos')->values([]);
    }

    public function testInsertWithNonArrayThrows()
    {
        $builder = new InsertBuilder();
        $this->expectException(\TypeError::class);
        $builder->table('produtos')->values('string_nao_array');
    }
}
