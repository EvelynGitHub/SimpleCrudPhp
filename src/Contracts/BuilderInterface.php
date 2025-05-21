<?php

declare(strict_types=1);

namespace SimplePhp\SimpleCrud\Contracts;

interface BuilderInterface
{
    public function getSql(): string;
    public function getBindings(): array;
}
