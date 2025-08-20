<?php

require __DIR__ . '/../vendor/autoload.php';

// $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
// $dotenv->safeLoad(); // carrega .env, mas não quebra se não existir

if (!function_exists('base_path')) {
    function base_path(string $path = ''): string
    {
        return rtrim(__DIR__ . '/..', '/') . ($path ? '/' . ltrim($path, '/') : '');
    }
}
