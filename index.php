<?php

require './Crud.php';
require './Connection.php';
require './Example.php';
require './Config.php';

$show = (new Example())->showExample(1);

echo $show;

