<?php
namespace App\Controllers;

use App\Services\Auth;

class AuthController
{
    public function login(): void
    {
        if (Auth::entrar(trim($_POST['email'] ?? ''), (string)($_POST['senha'] ?? ''))) redirect('');
        flash('err', 'E-mail ou senha incorretos.');
        redirect('?login=1');
    }

    public function sair(): void
    {
        Auth::sair();
        redirect('');
    }
}
