<?php

namespace SimplePhp\SimpleCrud\Contracts;

use SimplePhp\SimpleCrud\UseCases\QueryResult;

interface ExecutableInterface
{
    // public function execute();
    public function handle(BuilderInterface $builder): QueryResult;
    public function execute(BuilderInterface $builder): QueryResult;
    public function lastId(BuilderInterface $builder): int|null;
}