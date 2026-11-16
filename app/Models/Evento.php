<?php
namespace App\Models;

use App\Services\Database as DB;
use PDO;

class Evento
{
    public static function todos(int $limite = 100): array { return DB::q('SELECT * FROM evento ORDER BY data_inicio DESC LIMIT ' . (int)$limite)->fetchAll(); }
    public static function achar(int $id): ?array { return DB::q('SELECT * FROM evento WHERE id=?', [$id])->fetch() ?: null; }
    public static function opcoes(): array { return DB::q('SELECT id, titulo FROM evento ORDER BY data_inicio DESC LIMIT 60')->fetchAll(PDO::FETCH_KEY_PAIR); }

    public static function proximos(string $hoje, int $limite = 5): array
    {
        return DB::q('SELECT titulo, data_inicio AS d, tipo FROM evento WHERE COALESCE(data_fim, data_inicio) >= ? ORDER BY data_inicio LIMIT ' . (int)$limite, [$hoje])->fetchAll();
    }

    /* eventos marcados como públicos, do início do mês em diante */
    public static function paraTv(): array
    {
        return DB::q('SELECT * FROM evento WHERE publico = 1 AND COALESCE(data_fim, data_inicio) >= ? ORDER BY data_inicio LIMIT 12', [date('Y-m-01')])->fetchAll();
    }

    public static function salvar(?int $id, array $d, ?string $imagemNova, int $usuarioId): void
    {
        if ($id) {
            DB::q('UPDATE evento SET titulo=?,descricao=?,tipo=?,data_inicio=?,data_fim=?,local=?,publico=?,imagem=COALESCE(?,imagem) WHERE id=?',
                  [$d['titulo'], $d['descricao'], $d['tipo'], $d['data_inicio'], $d['data_fim'], $d['local'], $d['publico'], $imagemNova, $id]);
        } else {
            DB::q('INSERT INTO evento(titulo,descricao,tipo,data_inicio,data_fim,local,imagem,publico,criado_por) VALUES (?,?,?,?,?,?,?,?,?)',
                  [$d['titulo'], $d['descricao'], $d['tipo'], $d['data_inicio'], $d['data_fim'], $d['local'], $imagemNova, $d['publico'], $usuarioId]);
        }
    }

    public static function excluir(int $id): void
    {
        DB::q('UPDATE publicacao SET evento_id=NULL WHERE evento_id=?', [$id]);
        DB::q('DELETE FROM evento WHERE id=?', [$id]);
    }
}
