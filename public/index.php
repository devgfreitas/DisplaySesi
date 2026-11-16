<?php
/* Front controller: toda requisição entra por aqui. */
define('BASE_PATH', dirname(__DIR__));

if (is_file(BASE_PATH . '/vendor/autoload.php')) {
    require BASE_PATH . '/vendor/autoload.php';
} else {   // funciona sem o Composer
    spl_autoload_register(function (string $classe): void {
        $prefixo = 'App\\';
        if (strncmp($classe, $prefixo, strlen($prefixo)) !== 0) return;
        $arq = BASE_PATH . '/app/' . str_replace('\\', '/', substr($classe, strlen($prefixo))) . '.php';
        if (is_file($arq)) require $arq;
    });
}
require_once BASE_PATH . '/app/helpers.php';

date_default_timezone_set(config('app.timezone'));
session_set_cookie_params(['httponly' => true, 'samesite' => 'Lax']);
session_start();

set_exception_handler(function (Throwable $e): void {
    registrar_log($e->getMessage() . ' em ' . $e->getFile() . ':' . $e->getLine());
    http_response_code(500);
    echo '<!DOCTYPE html><meta charset="UTF-8"><title>Erro</title><body style="font-family:Arial;padding:2rem"><h1>Algo deu errado</h1><p>O erro foi registrado em storage/logs.</p>';
    if (config('app.debug')) echo '<pre>' . h($e) . '</pre>';
});

$router = new App\Services\Router();
(require BASE_PATH . '/routes/web.php')($router);
$router->dispatch();
