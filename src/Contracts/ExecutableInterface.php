<?php

namespace SimplePhp\SimpleCrud\Contracts;

interface ExecutableInterface
{
    // public function execute();
    public function handle(BuilderInterface $builder): array|int;
}