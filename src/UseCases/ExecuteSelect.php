<?php

namespace SimplePhp\SimpleCrud\UseCases;

use SimplePhp\SimpleCrud\Contracts\BuilderInterface;
use SimplePhp\SimpleCrud\Contracts\ExecutableInterface;
use SimplePhp\SimpleCrud\Core\SelectBuilder;

class ExecuteSelect implements ExecutableInterface
{
    public function __construct(private \PDO $pdo)
    {
    }

    public function handle(BuilderInterface $builder): array|int
    {
        // Aqui você pode implementar a lógica para executar a consulta
        // montada pelo SelectBuilder. Isso pode incluir a conexão ao banco
        // de dados e a execução da consulta SQL gerada.
        $stmt = $this->pdo->prepare($builder->getSql());
        $stmt->execute($builder->getBindings());
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

}