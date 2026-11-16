<?php
namespace App\Middleware;

use App\Services\Auth;

/* perfil:revisor => revisor ou administrador · perfil (sem argumento) => só administrador */
class PerfilMiddleware
{
    public function handle(array $perfis = []): void
    {
        if (!Auth::pode(...$perfis)) {
            flash('err', 'Seu perfil não tem acesso a esta área.');
            redirect('painel');
        }
    }
}
