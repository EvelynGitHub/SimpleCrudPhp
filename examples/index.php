<?php

require_once __DIR__ . "/../.env.php";
require_once __DIR__ . "/../src/Config.php";
require __DIR__ . "/../vendor/autoload.php";


use SimplePhp\SimpleCrud\Infra\Database\Crud;

// use SimplePhp\SimpleCrud\Crud;

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

    $select = Crud::select("nome, id")
        ->from("pessoa")
        ->where("nome = :nome AND id = ?", ["nome" => "Rodrigo", 123])
        ->getSQL();

} catch (\Throwable $th) {
    echo $th->getMessage();
    echo PHP_EOL;
}

$show = "Teste";

echo "<pre>";
var_dump($select);

