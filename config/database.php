<?php
/* Banco de dados SQLite: um único arquivo, fora da pasta pública. */
return [
    'arquivo' => BASE_PATH . '/database/central.sqlite',
    'schema'  => BASE_PATH . '/database/schema.sql',
    'seeds'   => BASE_PATH . '/database/seeds.sql',
    'semear'  => true,   // carrega os dados de demonstração se o banco estiver vazio
];
