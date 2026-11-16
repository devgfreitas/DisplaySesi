<?php
namespace App\Models;

use App\Services\Database as DB;

class Aniversariante
{
    public static function achar(int $id): ?array { return DB::q('SELECT * FROM aniversariante WHERE id=?', [$id])->fetch() ?: null; }

    /* todos, em ordem de mês e dia (a view agrupa por mês) */
    public static function todos(int $limite = 1000): array
    {
        return DB::q("SELECT a.*, t.nome AS turma FROM aniversariante a LEFT JOIN turma t ON t.id=a.turma_id
                      ORDER BY strftime('%m', a.data_nascimento), strftime('%d', a.data_nascimento), a.nome LIMIT " . (int)$limite)->fetchAll();
    }

    public static function criar(string $nome, ?int $turmaId, string $nascimento, ?string $foto): void
    {
        DB::q('INSERT INTO aniversariante(nome,turma_id,data_nascimento,foto,autorizado) VALUES (?,?,?,?,0)', [mb_substr($nome, 0, 120), $turmaId, $nascimento, $foto]);
    }

    public static function definirAutorizacao(array $ids, bool $autorizar, ?string $obs): void
    {
        foreach ($ids as $id) DB::q('UPDATE aniversariante SET autorizado=?, obs_autorizacao=? WHERE id=?', [$autorizar ? 1 : 0, $obs, (int)$id]);
    }

    public static function excluir(int $id): void { DB::q('DELETE FROM aniversariante WHERE id=?', [$id]); }

    /* só quem tem autorização e faz aniversário no mês atual */
    public static function paraTv(): array
    {
        return DB::q("SELECT nome, data_nascimento, foto FROM aniversariante WHERE autorizado = 1 AND strftime('%m', data_nascimento) = ? ORDER BY strftime('%d', data_nascimento)", [date('m')])->fetchAll();
    }
}
