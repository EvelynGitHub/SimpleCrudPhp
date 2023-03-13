<?php
use SimplePhp\SimpleCrud\Crud;

require_once './env.php';
require_once '../src/Config.php';
require_once '../src/Connection.php';
require_once '../src/Crud.php';
require_once './Example.php';


$crud = new Crud();
$show = (new Example($crud))->showExample(1);

echo $show;
