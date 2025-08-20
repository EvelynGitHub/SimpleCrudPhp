<?php

require_once __DIR__ . "/../.env.php";
require_once __DIR__ . "/../src/Config.php";
require __DIR__ . "/../vendor/autoload.php";


use ExamplesPhp\MyCustomQueryExample;
use SimplePhp\SimpleCrud\Facades\Database;
use SimplePhp\SimpleCrud\Facades\DB;


error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

echo '<pre>';

// ### Migrations e SEEDs ###

// Definindo o caminho e extensão das migrations e seeds
Database::migration(__DIR__ . '/migrations', Database::FILE_SQL);
Database::seed(__DIR__ . '/seeds', Database::FILE_PHP);
// Executando as migrations
// Database::executeOrCreateMigrate("nome_tabela_nova"); // Criando migrations para a tabela "nome_tabela_nova"
Database::executeOrCreateMigrate(); // Executando as migrations existentes
// Executando as seeds
Database::executeOrCreateSeed(); // Executando as seeds existentes


// ### Executando INSERT ###
echo '<hr/>';

try {

    echo "Inserindo Fulano e retornando seu id: ";

    $idFulano = DB::insert('usuarios')->values([
        "nome" => "Fulano 2",
        "email" => "fulano@example.com",
        "senha" => "fulano123"
    ])->execute()->lastInsertId;

    print_r($idFulano);

    echo '<hr/>';
    // ### Executando Consultas personalizadas ###
    echo 'Query Customizada executada:';

    // ## Registrando a consulta customizada ##
    DB::registerQuery('user_by_email', new MyCustomQueryExample());
    // ## Executando com parâmetro ##
    $result = DB::customQuery('user_by_email', ['email' => 'fulano@example.com'])->execute();

    print_r($result->fetch);

    // ### Executando INSERT Com SELECT###
    echo '<hr/>';

    // INSERINDO PRODUTO
    echo "Inserindo PRODUTO";

    $idProduto = DB::insert('produtos')->values([
        "nome" => 'Monitor',
        "descricao" => 'Monitor portátil',
        "preco" => "800.00",
        "estoque" => 2
    ])->execute()->lastInsertId;

    echo "Produto criado com ID: {$idProduto} <br>";

    echo '<hr/>';

    // Primeiro preciso criar o pedido
    echo "Inserindo o Novo PEDIDO: ";

    $idPedido = DB::insert('pedidos')->values([
        "id_usuario" => $idFulano,
        "data_pedido" => date('Y-m-d H:i:s'),
        "total" => "200.00"
    ])->execute()->lastInsertId;

    echo "Pedido criado com ID: {$idPedido} <br>";

    echo '<hr/>';

    echo "Inserindo os itens no pedido: ";

    $idPItensedido = DB::insert('itens_pedido')->values([
        "id_pedido" => $idPedido,
        "id_produto" => $idProduto,
        "quantidade" => 1,
        "preco_unitario" => "200.00"
    ])->execute()->lastInsertId;

    echo "Item Pedido criado com ID: {$idPItensedido} <br>";

    // $querySelectInsert = DB::select(["(SELECT MAX(pedidos.id) from pedidos) as id_pedido", "id_produto", "quantidade", "preco_unitario"])
    //     ->from("itens_pedido")
    //     ->join("pedidos", "pedidos.id = itens_pedido.id_pedido ")
    //     ->where("id_pedido", 1)
    //     ->group(["id_pedido", "id_produto", "quantidade", "preco_unitario"]);

    // $insertPedido = DB::insert("itens_pedido")
    //     ->valuesWhitSelect(
    //         ["id_pedido", "id_produto", "quantidade", "preco_unitario"],
    //         $querySelectInsert->getBuilder()
    //     );

    // print_r($insertPedido->getSql() . "<br>");
    // print_r($insertPedido->execute()->fetch);

    echo '<hr/>';

    echo "Obtendo pedidos do Fulano: ";

    $pedidos = DB::select(["pedidos.id", "produtos.nome", "quantidade", "preco_unitario"])
        ->from("itens_pedido")
        ->join("produtos", "produtos.id = itens_pedido.id_produto")
        ->join("pedidos", "pedidos.id = itens_pedido.id_pedido ")
        ->where("id_usuario", $idFulano)
        ->group(["id_pedido", "id_produto", "quantidade", "preco_unitario"])
        ->execute();

    print_r($pedidos->fetchAll);

    // ### Executando UPDATE ###
    echo '<hr/>';

    echo "Editando Fulano ";

    $crud = DB::update("usuarios")
        ->set([
            "nome" => "Fulano de Tal"
        ])
        ->where([
            "email" => "fulano@example.com",
            "id" => $idFulano ?? null
        ])->execute()->rowCount;

    print_r($crud);

    // ### Executando DELETE ###
    echo '<hr/>';

    echo "Deletando Fulano";

    $delete = DB::delete("usuarios")->execute();

    echo '<hr/>';

    echo "Deletando Item do Pedido";

    $delete = DB::delete("itens_pedido")->execute();

    echo '<hr/>';

    echo "Deletando Pedidos";


    $delete = DB::delete("pedidos")->execute();

    echo '<hr/>';
    echo "Deletando Produtos";

    $delete = DB::delete(table: "produtos")->execute();

} catch (\Throwable $th) {
    echo $th->getMessage() . "<br>";
}
