<?php
namespace App\Controllers;

use App\Models\Usuario;
use App\Services\View;

/* Cadastro de usuários e perfis (somente administrador). */
class UsuarioController
{
    public function index(int $id = 0): void
    {
        $x = ['nome' => '', 'email' => '', 'perfil' => 'editor', 'ativo' => 1];
        if ($id && ($r = Usuario::achar($id))) $x = $r; else $id = 0;
        View::render('usuarios/index', ['titulo' => 'Usuários', 'aba' => 'usuarios', 'id' => $id, 'x' => $x, 'usuarios' => Usuario::todos()]);
    }

    public function salvar(): void
    {
        $uid = (int)($_POST['id'] ?? 0);
        $nome = trim($_POST['nome'] ?? ''); $email = strtolower(trim($_POST['email'] ?? ''));
        $perfil = array_key_exists($_POST['perfil'] ?? '', config('app.perfis')) ? $_POST['perfil'] : 'editor';
        $senha = (string)($_POST['senha'] ?? ''); $ativo = !empty($_POST['ativo']) ? 1 : 0;
        $volta = 'painel/usuarios' . ($uid ? "/$uid/editar" : '');

        if ($nome === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) { flash('err', 'Informe nome e um e-mail válido.'); redirect($volta); }
        if (($senha !== '' || !$uid) && strlen($senha) < 6) { flash('err', 'A senha precisa ter ao menos 6 caracteres.'); redirect($volta); }
        if (Usuario::emailEmUso($email, $uid)) { flash('err', 'Já existe um usuário com este e-mail.'); redirect($volta); }

        if ($uid) {
            if ($uid === (int)usuario()['id']) { $ativo = 1; $perfil = 'administrador'; }   // o administrador não se bloqueia
            Usuario::atualizar($uid, $nome, $email, $perfil, $ativo);
            if ($senha !== '') Usuario::definirSenha($uid, $senha);
        } else {
            Usuario::criar($nome, $email, $senha, $perfil);
        }
        flash('ok', 'Usuário salvo.');
        redirect('painel/usuarios');
    }
}
