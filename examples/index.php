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
// Seeds::run(__DIR__ . '/seeds');
echo '<hr/>';


// ### Executando Consultas personalizadas ###
// ## Registrando a consulta ##
QueryBuilder::registerQuery('user_by_email', new MyCustomQueryExample());
// Crud::registerQuery('user_by_email', new MyCustomQueryExample()); // Também funciona assim

// ## Executando com parâmetro ##
$result = Crud::customQuery('user_by_email', ['email' => 'email1@gmail.com'])->execute('fetch');

print_r($result);


// ### Executando INSERT ###
echo '<hr/>';

$idFulano = Crud::insert("usuarios", [
    "nome" => "Fulano",
    "email" => "fulano@example.com",
])->execute("lastId");

echo "Inserindo Fulano e retornando seu id: ";
print_r($idFulano);



// ### Executando INSERT ###
echo '<hr/>';

$crud = Crud::update("usuarios", [
    "nome" => "Fulano de Tal",
    "email" => "fulano@example.com",
])->execute();

echo "Editando Fulano e retornando seu id: ";
print_r($crud);

// try {

// $crud = Crud::insert("oi", ["nome" => "Aqui"])->getSQL();

// $select = Crud::select("nome, id")
//     ->from("pessoa")
//     ->where("nome = :nome AND id = ?", ["nome" => "Rodrigo", 123])
//     ->getSQL();

// $subSubQuery = Crud::select("id")
//     ->from("transactions")
//     ->where("status = ?", ["approved"]);

// $subQuery = Crud::select("user_id")
//     ->from("orders")
//     ->where("transaction_id IN (?)", [$subSubQuery]);

// $query = Crud::select("name")
//     ->from("users")
//     ->where("id IN (?)", [$subQuery]);


// $subSubQuery = Crud2::select("id")
//     ->from("transactions")
//     ->where("status = ?", ["approved"]);

// $subQuery = Crud2::select("user_id")
//     ->from("orders")
//     ->where("transaction_id IN (?)", [$subSubQuery]);

// $query = Crud2::select("name")
//     ->from("users")
//     ->where("id IN (?)", [$subQuery]);


// // $query = Crud2::select('*')->from('usuarios')->where('id = :id', ['id' => 2]);


//     echo '<br>';
//     $subSubQuery = Crud3::select("id")
//         ->from("transactions")
//         ->where("status = ?", ["approved"]);

//     $subQuery = Crud3::select("user_id")
//         ->from("orders")
//         ->where("transaction_id IN (?)", [$subSubQuery]);

//     $query = Crud3::select("name")
//         ->from("users")
//         ->where("id IN (?)", [$subQuery]);


//     $select = $query->getQuery();

//     echo "<pre>";

//     var_dump('SELECT:>> ', $select);

//     var_dump("Execute: ", $query->execute('fetch'));


// } catch (\Throwable $th) {
//     echo $th->getMessage();
//     echo PHP_EOL;
// }
