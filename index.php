<?php

require './Config.php';
require './ConnectionException.php';
require './Connection.php';
require './Crud.php';
require './Example.php';

echo "<h1>INDEX de Exemplo</h1>";

$show = (new Example())->showExample();

var_dump($show);
