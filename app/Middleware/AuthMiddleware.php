<?php
namespace App\Middleware;

use App\Services\Auth;

/* Só deixa passar quem está logado. */
class AuthMiddleware
{
    public function handle(array $args = []): void
    {
        if (!Auth::usuario()) redirect('?login=1');
    }
}
