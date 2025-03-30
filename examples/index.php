<?php

require_once __DIR__ . "/../.env.php";
require_once __DIR__ . "/../src/Config.php";
require __DIR__ . "/../vendor/autoload.php";


use ExamplesPhp\MyCustomQueryExample;
use SimplePhp\SimpleCrud\Core\Entity\QueryBuilder;
use SimplePhp\SimpleCrud\Infra\Database\Crud;


error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);


// require_once __DIR__ . "/../src/Config.php";
// require_once __DIR__ . "/../src/ConnectionException.php";
// require_once __DIR__ . "/../src/Infra/Database/Connection.php";
// require_once __DIR__ . "/../src/Infra/Database/Crud.php";
// require_once __DIR__ . "/Example.php";


// $crud = new Crud();
// $show = (new Example($crud))->showExample(1);

try {

    // $crud = Crud::insert("oi", ["nome" => "Aqui"])->getSQL();

    // $select = Crud::select("nome, id")
    //     ->from("pessoa")
    //     ->where("nome = :nome AND id = ?", ["nome" => "Rodrigo", 123])
    //     ->getSQL();

    $subSubQuery = Crud::select("id")
        ->from("transactions")
        ->where("status = ?", ["approved"]);

    $subQuery = Crud::select("user_id")
        ->from("orders")
        ->where("transaction_id IN (?)", [$subSubQuery]);

    $query = Crud::select("name")
        ->from("users")
        ->where("id IN (?)", [$subQuery]);

    $select = $query->getQuery();
} catch (\Throwable $th) {
    echo $th->getMessage();
    echo PHP_EOL;
}


echo "<pre>";
var_dump($select);



// die();


// ### Consultas personalizadas pelo usuário do pacote ###

// Registrando a consulta
QueryBuilder::registerQuery('user_by_email', new MyCustomQueryExample());

// Executando com parâmetro
$result = Crud::customQuery('user_by_email', ['email' => 'user@example.com'])->execute();

echo $result;