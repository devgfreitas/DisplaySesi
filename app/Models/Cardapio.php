<?php
namespace App\Models;

use App\Services\Database as DB;

class Cardapio
{
    public static function todos(int $limite = 40): array { return DB::q('SELECT * FROM cardapio_semanal ORDER BY semana_inicio DESC LIMIT ' . (int)$limite)->fetchAll(); }
    public static function achar(int $id): ?array { return DB::q('SELECT * FROM cardapio_semanal WHERE id=?', [$id])->fetch() ?: null; }
    public static function publicados(int $limite = 8): array { return DB::q("SELECT id, semana_inicio FROM cardapio_semanal WHERE status='publicado' ORDER BY semana_inicio DESC LIMIT " . (int)$limite)->fetchAll(); }

    public static function criarSemana(string $segunda, int $usuarioId): int
    {
        $id = DB::q('SELECT id FROM cardapio_semanal WHERE semana_inicio=?', [$segunda])->fetchColumn();
        if ($id) return (int)$id;
        DB::q('INSERT INTO cardapio_semanal(semana_inicio,titulo,criado_por) VALUES (?,?,?)', [$segunda, 'Cardápio da semana', $usuarioId]);
        return DB::ultimoId();
    }

    /* [dia => [refeição => descrição]] */
    public static function grade(int $id): array
    {
        $g = [];
        foreach (DB::q('SELECT dia_semana, refeicao, descricao FROM cardapio_dia WHERE cardapio_id = ?', [$id])->fetchAll() as $r) {
            $g[(int)$r['dia_semana']][$r['refeicao']] = $r['descricao'];
        }
        return $g;
    }

    public static function salvar(int $id, string $titulo, ?string $imagem, string $status, array $celulas): void
    {
        DB::q('UPDATE cardapio_semanal SET titulo=?, imagem=COALESCE(?,imagem), status=? WHERE id=?', [$titulo, $imagem, $status, $id]);
        foreach (array_keys(config('app.dias')) as $dia) {
            foreach (array_keys(config('app.refeicoes')) as $ref) {
                $v = trim($celulas[$dia][$ref] ?? '');
                if ($v === '') {
                    DB::q('DELETE FROM cardapio_dia WHERE cardapio_id=? AND dia_semana=? AND refeicao=?', [$id, $dia, $ref]);
                } else {
                    DB::q('INSERT INTO cardapio_dia(cardapio_id,dia_semana,refeicao,descricao) VALUES (?,?,?,?)
                           ON CONFLICT(cardapio_id,dia_semana,refeicao) DO UPDATE SET descricao=excluded.descricao', [$id, $dia, $ref, mb_substr($v, 0, 160)]);
                }
            }
        }
    }

    public static function definirStatus(int $id, string $status): void { DB::q('UPDATE cardapio_semanal SET status=? WHERE id=?', [$status, $id]); }
    public static function excluir(int $id): void { DB::q('DELETE FROM cardapio_semanal WHERE id=?', [$id]); }

    /* semana publicada que contém hoje; se não houver, a próxima publicada */
    public static function atualParaTv(string $hoje): ?array
    {
        $c = DB::q("SELECT * FROM cardapio_semanal WHERE status='publicado' AND semana_inicio <= ? AND date(semana_inicio,'+6 day') >= ? LIMIT 1", [$hoje, $hoje])->fetch();
        if (!$c) $c = DB::q("SELECT * FROM cardapio_semanal WHERE status='publicado' AND semana_inicio > ? ORDER BY semana_inicio LIMIT 1", [$hoje])->fetch();
        return $c ?: null;
    }

    public static function ultimoPublicadoAte(string $hoje): ?array
    {
        return DB::q("SELECT * FROM cardapio_semanal WHERE status='publicado' AND semana_inicio <= ? ORDER BY semana_inicio DESC LIMIT 1", [$hoje])->fetch() ?: null;
    }
}
