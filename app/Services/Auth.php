<?php
namespace App\Services;

use App\Models\Usuario;

/* Login por sessão e verificação de perfil. O administrador pode tudo. */
class Auth
{
    public static function usuario(): ?array
    {
        static $cache = [];
        $id = (int)($_SESSION['uid'] ?? 0);
        if (!$id) return null;
        if (!array_key_exists($id, $cache)) $cache[$id] = Usuario::ativoPorId($id);
        return $cache[$id];
    }

    public static function entrar(string $email, string $senha): bool
    {
        $u = Usuario::porEmail($email);
        if ($u && (int)$u['ativo'] === 1 && password_verify($senha, $u['senha_hash'])) {
            session_regenerate_id(true);
            $_SESSION['uid'] = (int)$u['id'];
            return true;
        }
        usleep(400000);   // atrasa tentativas erradas
        return false;
    }

    public static function sair(): void
    {
        $_SESSION = [];
        session_destroy();
    }

    /* pode('revisor') = revisor ou administrador; pode() = só administrador */
    public static function pode(string ...$perfis): bool
    {
        $u = self::usuario();
        if (!$u) return false;
        return $u['perfil'] === 'administrador' || in_array($u['perfil'], $perfis, true);
    }
}
