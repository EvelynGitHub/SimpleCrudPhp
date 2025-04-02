<?php

declare(strict_types=1);

namespace SimplePhp\SimpleCrud\Core\Interfaces;

use PDO;

interface DatabaseConnectionInterface
{
    public static function getInstance(): PDO;
}
