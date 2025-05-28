<?php

namespace SimplePhp\SimpleCrud\Helpers;

use SimplePhp\SimpleCrud\Core\QueryBuilder;

class SQLUtils
{
    public static function validateQuery($query)
    {
        // Implementar lógica de validação de consulta SQL
        // Retornar true se a consulta for válida, caso contrário, false
    }

    public static function parseBindings($query, $bindings)
    {
        // Implementar lógica para analisar e substituir bindings na consulta
        // Retornar a consulta com os bindings aplicados
    }

    public static function sanitizeInput($input)
    {
        // Implementar lógica para sanitizar entradas de usuário
        // Retornar a entrada sanitizada
    }

    /**
     * Aplica filtros complexos a partir de um array, recursivamente.
     */
    public static function applyFilters(QueryBuilder $qb, array $filters, string $boolOperator = 'AND')
    {
        foreach ($filters as $key => $value) {
            $method = strtolower($boolOperator) === 'or' ? 'orWhere' : 'where';

            if (in_array(strtoupper($key), ['OR', 'AND'])) {
                $qb->$method(function ($q) use ($value, $key) {
                    foreach ($value as $condition) {
                        self::applyFilters($q, $condition, strtoupper($key));
                    }
                });
                continue;
            }

            if (is_array($value)) {
                foreach ($value as $op => $val) {
                    $upperOp = strtoupper($op);
                    if ($upperOp === 'IN') {
                        $qb->$method(function ($q) use ($key, $val) {
                            $q->whereIn($key, $val);
                        });
                    } elseif ($upperOp === 'NULL') {
                        $qb->$method($key, 'IS', null);
                    } elseif ($upperOp === 'NOT NULL') {
                        $qb->$method($key, 'IS NOT', null);
                    } elseif ($upperOp === 'NOT') {
                        $qb->$method(function ($q) use ($key, $val) {
                            foreach ($val as $op2 => $val2) {
                                $q->where($key, $op2 === 'IN' ? 'NOT IN' : $op2, $val2);
                            }
                        });
                    } else {
                        $qb->$method($key, $op, $val);
                    }
                }
            } else {
                $qb->$method($key, '=', $value);
            }
        }
    }
}