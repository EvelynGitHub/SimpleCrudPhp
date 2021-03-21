<?php

require_once './env.php';
require_once '../src/Config.php';
require_once '../src/Connection.php';
require_once '../src/Crud.php';
require_once './Example.php';


$show = (new Example())->showExample(1);

echo $show;
