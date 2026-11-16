<?php
namespace App\Services;

use App\Middleware\AuthMiddleware;
use App\Middleware\CsrfMiddleware;
use App\Middleware\PerfilMiddleware;

/* Roteador simples: rotas com {parametros}, métodos GET/POST e middlewares ('auth', 'csrf', 'perfil:revisor'). */
class Router
{
    private array $rotas = [];

    public function get(string $caminho, string $acao, array $mw = []): void  { $this->add('GET', $caminho, $acao, $mw); }
    public function post(string $caminho, string $acao, array $mw = []): void { $this->add('POST', $caminho, $acao, array_merge(['csrf'], $mw)); }

    private function add(string $metodo, string $caminho, string $acao, array $mw): void
    {
        $re = '#^' . preg_replace('#\{(\w+)\}#', '(?P<$1>[^/]+)', $caminho) . '$#';
        $this->rotas[] = [$metodo, $re, $acao, $mw];
    }

    public function dispatch(): void
    {
        $metodo = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $caminho = self::caminho();
        foreach ($this->rotas as [$m, $re, $acao, $mw]) {
            if ($m !== $metodo || !preg_match($re, $caminho, $achou)) continue;
            $params = array_values(array_filter($achou, 'is_string', ARRAY_FILTER_USE_KEY));
            foreach ($mw as $spec) $this->middleware($spec);
            [$classe, $metodoCtl] = explode('@', $acao);
            $cls = 'App\\Controllers\\' . $classe;
            (new $cls())->$metodoCtl(...$params);
            return;
        }
        http_response_code(404);
        echo '<!DOCTYPE html><meta charset="UTF-8"><title>404</title><body style="font-family:Arial;padding:2rem"><h1>Página não encontrada</h1><p><a href="' . h(url('')) . '">Voltar</a></p>';
    }

    private function middleware(string $spec): void
    {
        [$nome, $arg] = array_pad(explode(':', $spec, 2), 2, null);
        $mapa = ['auth' => AuthMiddleware::class, 'csrf' => CsrfMiddleware::class, 'perfil' => PerfilMiddleware::class];
        (new $mapa[$nome]())->handle($arg === null ? [] : explode(',', $arg));
    }

    /* caminho da requisição sem a pasta onde o sistema está instalado */
    private static function caminho(): string
    {
        $path = (string)parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
        $base = base_url();                       // ex.: /projeto/public
        $raiz = rtrim(dirname($base), '/\\');     // ex.: /projeto
        if ($base !== '' && strpos($path, $base) === 0) $path = substr($path, strlen($base));
        elseif ($raiz !== '' && strpos($path, $raiz) === 0) $path = substr($path, strlen($raiz));
        return '/' . trim(rawurldecode($path), '/');
    }
}
