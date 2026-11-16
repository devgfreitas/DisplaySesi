<?php
/* Prepara o ambiente dos testes (sem servidor web). */
define('BASE_PATH', dirname(__DIR__));

if (is_file(BASE_PATH . '/vendor/autoload.php')) {
    require BASE_PATH . '/vendor/autoload.php';
} else {
    spl_autoload_register(function (string $classe): void {
        $prefixo = 'App\\';
        if (strncmp($classe, $prefixo, strlen($prefixo)) !== 0) return;
        $arq = BASE_PATH . '/app/' . str_replace('\\', '/', substr($classe, strlen($prefixo))) . '.php';
        if (is_file($arq)) require $arq;
    });
}
require_once BASE_PATH . '/app/helpers.php';
date_default_timezone_set(config('app.timezone'));
