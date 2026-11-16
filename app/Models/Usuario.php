<?php
namespace App\Models;

use App\Services\Database as DB;

class Usuario
{
    public static function ativoPorId(int $id): ?array { return DB::q('SELECT * FROM usuario WHERE id=? AND ativo=1', [$id])->fetch() ?: null; }
    public static function achar(int $id): ?array { return DB::q('SELECT * FROM usuario WHERE id=?', [$id])->fetch() ?: null; }
    public static function porEmail(string $email): ?array { return DB::q('SELECT * FROM usuario WHERE lower(email)=lower(?)', [$email])->fetch() ?: null; }
    public static function todos(): array { return DB::q('SELECT * FROM usuario ORDER BY nome')->fetchAll(); }

    public static function emailEmUso(string $email, int $ignorarId = 0): bool
    {
        return (bool)DB::q('SELECT id FROM usuario WHERE lower(email)=lower(?) AND id<>?', [$email, $ignorarId])->fetchColumn();
    }

    public static function criar(string $nome, string $email, string $senha, string $perfil): void
    {
        DB::q('INSERT INTO usuario(nome,email,senha_hash,perfil,criado_em) VALUES (?,?,?,?,?)',
              [$nome, $email, password_hash($senha, PASSWORD_DEFAULT), $perfil, agora()]);
    }

    public static function atualizar(int $id, string $nome, string $email, string $perfil, int $ativo): void
    {
        DB::q('UPDATE usuario SET nome=?, email=?, perfil=?, ativo=? WHERE id=?', [$nome, $email, $perfil, $ativo, $id]);
    }

    public static function definirSenha(int $id, string $senha): void
    {
        DB::q('UPDATE usuario SET senha_hash=? WHERE id=?', [password_hash($senha, PASSWORD_DEFAULT), $id]);
    }
}
