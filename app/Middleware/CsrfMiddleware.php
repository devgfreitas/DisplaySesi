<?php
namespace App\Middleware;

use App\Services\Csrf;

/* Todo POST precisa do token da sessão. */
class CsrfMiddleware
{
    public function handle(array $args = []): void
    {
        if (!Csrf::valido()) {
            flash('err', 'Sessão expirada. Tente novamente.');
            $volta = $_SERVER['HTTP_REFERER'] ?? '';
            if ($volta !== '') { header('Location: ' . $volta); exit; }
            redirect('');
        }
    }
}
