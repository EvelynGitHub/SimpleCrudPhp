<?php
use SimplePhp\SimpleCrud\Crud;

require_once __DIR__ . "/../.env.php";
require_once __DIR__ . "/../src/Config.php";
require_once __DIR__ . "/../src/ConnectionException.php";
require_once __DIR__ . "/../src/Connection.php";
require_once __DIR__ . "/../src/Crud.php";
require_once __DIR__ . "/Example.php";


$crud = new Crud();
$show = (new Example($crud))->showExample(1);

var_dump($show);
