<?php

declare(strict_types=1);

namespace ExamplesPhp;

use SimplePhp\SimpleCrud\Core\Interfaces\CustomQuery;


class MyCustomQueryExample implements CustomQuery
{
    private array $params = [];

    public function apply(): string
    {
        return "SELECT * FROM users WHERE email = :email";
    }

    public function setParams(array $params): void
    {
        $this->params = $params;
    }

    public function getParams(): array
    {
        return $this->params;
    }
}