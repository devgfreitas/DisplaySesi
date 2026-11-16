<?php
namespace App\Models;

use App\Services\Database as DB;
use PDO;

class Turma
{
    /* [id => nome] das turmas ativas */
    public static function ativas(): array { return DB::q('SELECT id, nome FROM turma WHERE ativo=1 ORDER BY nome')->fetchAll(PDO::FETCH_KEY_PAIR); }
    public static function todasComContagem(): array
    {
        return DB::q('SELECT t.*, (SELECT COUNT(*) FROM publicacao_turma pt WHERE pt.turma_id=t.id) AS n FROM turma t ORDER BY t.nome')->fetchAll();
    }
    public static function existe(string $nome): bool { return (bool)DB::q('SELECT 1 FROM turma WHERE lower(nome)=lower(?)', [$nome])->fetchColumn(); }
    public static function criar(string $nome): void { DB::q('INSERT INTO turma(nome) VALUES (?)', [mb_substr($nome, 0, 60)]); }
    public static function alternar(int $id): void { DB::q('UPDATE turma SET ativo = 1 - ativo WHERE id=?', [$id]); }
    /* [nome em minúsculas => id], usado na importação de aniversariantes */
    public static function mapaPorNome(): array
    {
        $m = [];
        foreach (DB::q('SELECT id, nome FROM turma')->fetchAll() as $t) $m[mb_strtolower($t['nome'])] = (int)$t['id'];
        return $m;
    }
}
