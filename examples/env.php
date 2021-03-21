<?php

// É importante não deixa espaços em branco antes e depois do "="

// Qual banco? (melhor compatibilidade com mysql)
putenv("DB_DRIVE=mysql");

putenv("DB_HOST=localhost");
// Porta que o banco está usando
putenv("DB_PORT=3306");
// Nome do banco de dados
putenv("DB_NAME=test");
// Nome do seu usuário do banco
putenv("DB_USER_NAME=root");
// Senha do usuário do banco
putenv("DB_USER_PASSWD=");
