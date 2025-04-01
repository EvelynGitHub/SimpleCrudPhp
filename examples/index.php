<?php

require_once __DIR__ . "/../.env.php";
require_once __DIR__ . "/../src/Config.php";
require __DIR__ . "/../vendor/autoload.php";


use ExamplesPhp\MyCustomQueryExample;
use SimplePhp\SimpleCrud\Core\Entity\QueryBuilder;
use SimplePhp\SimpleCrud\Infra\Database\Crud;
use SimplePhp\SimpleCrud\Infra\Database\Migrations;
use SimplePhp\SimpleCrud\Infra\Database\Seeds;


error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

echo '<pre>';

// ### Executando as Migrations ###

Migrations::run(__DIR__ . '/migrations');
echo '<hr/>';

// ### Executando as Seeds ###
Seeds::run(__DIR__ . '/seeds');
echo '<hr/>';


// ### Executando Consultas personalizadas ###
// ## Registrando a consulta ##
echo 'Query Customizada executada:';
QueryBuilder::registerQuery('user_by_email', new MyCustomQueryExample());
// Crud::registerQuery('user_by_email', new MyCustomQueryExample()); // Também funciona assim

// ## Executando com parâmetro ##
$result = Crud::customQuery('user_by_email', ['email' => 'email1@gmail.com'])->execute('fetch');

print_r($result);


// ### Executando INSERT ###
echo '<hr/>';

try {

    echo "Inserindo Fulano e retornando seu id: ";

    $idFulano = Crud::insert("usuarios", [
        "nome" => "Fulano",
        "email" => "fulano@example.com",
        "senha" => "fulano123",
    ])->execute("lastId");

    print_r($idFulano);

} catch (\Throwable $th) {
    echo $th->getMessage() . "<br>";
}

// ### Executando INSERT Com SELECT###
echo '<hr/>';

try {
    // Primeiro preciso criar o pedido

    echo "Inserindo os Novo do PEDIDO: ";
    $selectNovoPedido = Crud::select("id_usuario, total")
        ->from("pedidos")
        ->where("id = :id", ["id" => 1]);

    $insertNovoPedido = Crud::insert("pedidos", $selectNovoPedido, "id_usuario, total")
        ->execute("lastId");

    print_r($insertNovoPedido . "<br>");

    // Depois insere os itens nesse pedido criado
    echo "Inserindo os itens do pedido 1 no novo pedido: ";

    $querySelectInsert = Crud::select("(SELECT MAX(pedidos.id) from pedidos) as id_pedido, id_produto, quantidade, preco_unitario")
        ->from("itens_pedido")
        ->join("pedidos", "pedidos.id = itens_pedido.id_pedido ")
        ->where("id_pedido = :id", ["id" => 1])
        ->group(["id_pedido", "id_produto", "quantidade", "preco_unitario"]);

    $insertPedido = Crud::insert("itens_pedido", $querySelectInsert, "id_pedido, id_produto, quantidade, preco_unitario");

    print_r($insertPedido->getQuery() . "<br>");
    print_r($insertPedido->execute("lastId"));

} catch (\Throwable $th) {
    echo $th->getMessage() . "<br>";
}

// ### Executando UPDATE ###
echo '<hr/>';

try {

    echo "Editando Fulano";

    $crud = Crud::update(
        "usuarios",
        [
            "nome" => "Fulano de Tal"
        ]
    )
        ->where("email = :email OR id = :id", [
            "email" => "fulano@example.com",
            "id" => $idFulano ?? null
        ])->execute();

    print_r($crud);

} catch (\Throwable $th) {
    echo $th->getMessage() . "<br>";
}



// ### Executando UPDATE ###
echo '<hr/>';

try {

    echo " Selects <br> ";

    echo "<p>----------------------------</p>";
    echo "<p>-- Buscar todos os usuários</p>";

    $select1 = Crud::select("*")->from("usuarios")->execute("fetchAll");
    print_r($select1);

    echo "<p>----------------------------</p>";
    echo "<p>-- Buscar os itens de um pedido específico com detalhes dos produtos</p>";

    $select2 = Crud::select("itens_pedido.id, produtos.nome, itens_pedido.quantidade, itens_pedido.preco_unitario")
        ->from("itens_pedido")
        ->join("produtos", "itens_pedido.id_produto = produtos.id")
        ->where("itens_pedido.id_pedido = :id", ["id" => 1]);

    print_r($select2->getQuery() . "<br>");
    print_r($select2->execute("fetchAll"));

    echo "<p>----------------------------</p>";
    echo "<p>-- Buscar nomes de usuários que fizeram pedidos</p>";

    // -- Sub-subquery: Pegar IDs dos produtos acima de um determinado valor
    $subSubQuery = Crud::select("id")
        ->from("produtos")
        ->where("preco > ?", [1000]);

    // -- Subquery: Pegar IDs dos pedidos que contêm esses produtos
    $subQuery = Crud::select("DISTINCT id_pedido")
        ->from("itens_pedido")
        ->where("id_produto IN (?)", [$subSubQuery]);

    // -- Query principal: Pegar os nomes dos usuários que fizeram esses pedidos
    $query = Crud::select("nome")
        ->from("usuarios")
        ->where("id IN (?)", [
            Crud::select("id_usuario")
                ->from("pedidos")
                ->where("id IN (?)", [$subQuery])
        ]);

    print_r($query->getQuery() . "<br>");
    print_r($query->execute("fetch"));

} catch (\Throwable $th) {
    echo $th->getMessage() . "<br>";
}


// ### Executando DELETE ###
echo '<hr/>';

try {

    echo "Deletando Fulano";

    $delete = Crud::delete("usuarios")
        ->where("email = :email OR id = :id", [
            "email" => "fulano@example.com",
            "id" => $idFulano ?? null
        ])->execute();

    print_r($delete);

} catch (\Throwable $th) {
    echo $th->getMessage() . "<br>";
}
