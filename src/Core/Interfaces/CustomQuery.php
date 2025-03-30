<?php

declare(strict_types=1);

namespace SimplePhp\SimpleCrud\Core\Interfaces;

interface CustomQuery
{
    public function apply(): string;
    public function setParams(array $params): void;
    public function getParams(): array;
}
