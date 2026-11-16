<?php
namespace App\Models;

use App\Services\Database as DB;
use PDO;

/* Publicações: comunicados, avisos, notícias, recados, provas e tarefas, com fluxo de revisão. */
class Publicacao
{
    /* ---------- leitura ---------- */
    public static function achar(int $id): ?array { return DB::q('SELECT * FROM publicacao WHERE id=?', [$id])->fetch() ?: null; }

    public static function turmasIds(int $id): array
    {
        return array_map('intval', DB::q('SELECT turma_id FROM publicacao_turma WHERE publicacao_id=?', [$id])->fetchAll(PDO::FETCH_COLUMN));
    }

    public static function historico(int $id): array
    {
        return DB::q('SELECT r.*, u.nome FROM revisao_publicacao r JOIN usuario u ON u.id=r.usuario_id WHERE r.publicacao_id=? ORDER BY r.id', [$id])->fetchAll();
    }

    /* busca por texto, categoria, status, turma e período; quem não revisa vê só as próprias */
    public static function listar(array $f, ?int $somenteAutor): array
    {
        $w = ['1=1']; $a = [];
        if ($somenteAutor) { $w[] = 'p.autor_id=?'; $a[] = $somenteAutor; }
        if ($f['q'] !== '') { $w[] = '(p.titulo LIKE ? OR p.descricao LIKE ?)'; $a[] = '%' . $f['q'] . '%'; $a[] = '%' . $f['q'] . '%'; }
        if (isset(config('app.tipos')[$f['tipo']])) { $w[] = 'p.tipo=?'; $a[] = $f['tipo']; }
        if (isset(config('app.status')[$f['status']])) { $w[] = 'p.status=?'; $a[] = $f['status']; }
        if ($f['turma']) { $w[] = 'EXISTS (SELECT 1 FROM publicacao_turma pt WHERE pt.publicacao_id=p.id AND pt.turma_id=?)'; $a[] = $f['turma']; }
        if ($f['de'] !== '') { $w[] = 'date(COALESCE(p.inicio_exibicao,p.criado_em))>=?'; $a[] = $f['de']; }
        if ($f['ate'] !== '') { $w[] = 'date(COALESCE(p.inicio_exibicao,p.criado_em))<=?'; $a[] = $f['ate']; }
        return DB::q("SELECT p.*, u.nome AS autor,
                        (SELECT group_concat(t.nome, ', ') FROM publicacao_turma pt JOIN turma t ON t.id=pt.turma_id WHERE pt.publicacao_id=p.id) AS turmas,
                        (SELECT comentario FROM revisao_publicacao r WHERE r.publicacao_id=p.id AND r.acao='rejeitada' ORDER BY r.id DESC LIMIT 1) AS motivo
                      FROM publicacao p JOIN usuario u ON u.id=p.autor_id
                      WHERE " . implode(' AND ', $w) . ' ORDER BY p.atualizado_em DESC LIMIT 200', $a)->fetchAll();
    }

    public static function fila(): array
    {
        return DB::q("SELECT p.*, u.nome AS autor,
                        (SELECT group_concat(t.nome, ', ') FROM publicacao_turma pt JOIN turma t ON t.id=pt.turma_id WHERE pt.publicacao_id=p.id) AS turmas
                      FROM publicacao p JOIN usuario u ON u.id=p.autor_id WHERE p.status='em_revisao' ORDER BY p.atualizado_em")->fetchAll();
    }

    /* ---------- escrita ---------- */
    public static function salvar(?int $id, array $d, array $turmas): int
    {
        $n = agora();
        if ($id) {
            DB::q('UPDATE publicacao SET tipo=?,titulo=?,descricao=?,disciplina=?,data_ref=?,prazo=?,imagem=?,evento_id=?,publico_alvo=?,visibilidade=?,exibir_tv=?,
                   status=?,prioridade=?,duracao_seg=?,inicio_exibicao=?,fim_exibicao=?,atualizado_em=? WHERE id=?',
                  [$d['tipo'], $d['titulo'], $d['descricao'], $d['disciplina'], $d['data_ref'], $d['prazo'], $d['imagem'], $d['evento_id'], $d['publico_alvo'], $d['visibilidade'],
                   $d['exibir_tv'], $d['status'], $d['prioridade'], $d['duracao_seg'], $d['inicio_exibicao'], $d['fim_exibicao'], $n, $id]);
        } else {
            DB::q('INSERT INTO publicacao(tipo,titulo,descricao,disciplina,data_ref,prazo,imagem,autor_id,evento_id,publico_alvo,visibilidade,exibir_tv,status,prioridade,duracao_seg,inicio_exibicao,fim_exibicao,criado_em,atualizado_em)
                   VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)',
                  [$d['tipo'], $d['titulo'], $d['descricao'], $d['disciplina'], $d['data_ref'], $d['prazo'], $d['imagem'], $d['autor_id'], $d['evento_id'], $d['publico_alvo'],
                   $d['visibilidade'], $d['exibir_tv'], $d['status'], $d['prioridade'], $d['duracao_seg'], $d['inicio_exibicao'], $d['fim_exibicao'], $n, $n]);
            $id = DB::ultimoId();
        }
        DB::q('DELETE FROM publicacao_turma WHERE publicacao_id=?', [$id]);
        foreach ($turmas as $t) DB::q('INSERT OR IGNORE INTO publicacao_turma VALUES (?,?)', [$id, (int)$t]);
        self::sincronizarItemTv($id, (int)$d['exibir_tv'], (int)$d['prioridade'], (int)$d['duracao_seg']);
        return $id;
    }

    /* mantém o ITEM_EXIBICAO da TV em dia com a publicação */
    public static function sincronizarItemTv(int $id, int $tv, int $prio, int $dur): void
    {
        if ($tv) {
            DB::q('INSERT INTO item_exibicao(conteudo_tv_id,publicacao_id,prioridade,duracao_seg,ativo) VALUES (1,?,?,?,1)
                   ON CONFLICT(publicacao_id) DO UPDATE SET prioridade=excluded.prioridade,duracao_seg=excluded.duracao_seg,ativo=1', [$id, $prio, $dur]);
        } else {
            DB::q('DELETE FROM item_exibicao WHERE publicacao_id=?', [$id]);
        }
    }

    public static function mudarStatus(int $id, string $status): void
    {
        DB::q('UPDATE publicacao SET status=?, atualizado_em=? WHERE id=?', [$status, agora(), $id]);
    }

    public static function registrarRevisao(int $id, int $usuarioId, string $acao, ?string $comentario = null): void
    {
        DB::q('INSERT INTO revisao_publicacao(publicacao_id,usuario_id,acao,comentario,criado_em) VALUES (?,?,?,?,?)', [$id, $usuarioId, $acao, $comentario ?: null, agora()]);
    }

    /* ---------- TV pública: só o que é público, geral, aprovado e dentro da validade ---------- */
    public static function paraTv(): array
    {
        $n = agora();
        return DB::q("SELECT p.*, i.prioridade AS ip, i.duracao_seg AS idur
                      FROM publicacao p JOIN item_exibicao i ON i.publicacao_id = p.id
                      WHERE i.ativo = 1 AND p.status = 'aprovado' AND p.exibir_tv = 1 AND p.visibilidade = 'publico'
                        AND p.publico_alvo = 'geral' AND p.tipo IN ('comunicado','aviso','noticia')
                        AND (p.inicio_exibicao IS NULL OR p.inicio_exibicao <= ?) AND (p.fim_exibicao IS NULL OR p.fim_exibicao >= ?)
                      ORDER BY i.prioridade DESC, COALESCE(p.inicio_exibicao, p.criado_em) DESC", [$n, $n])->fetchAll();
    }

    /* ---------- área de consulta ---------- */
    public static function paraConsulta(int $turmaId, string $perfil): array
    {
        $vis = $perfil === 'comunidade' ? "('publico','interno')" : "('publico')";   // privado nunca aparece
        $n = agora();
        return DB::q("SELECT p.* FROM publicacao p
                      WHERE p.status = 'aprovado' AND p.tipo <> 'recado_individual' AND p.visibilidade IN $vis
                        AND (p.inicio_exibicao IS NULL OR p.inicio_exibicao <= ?) AND (p.fim_exibicao IS NULL OR p.fim_exibicao >= ?)
                        AND (p.publico_alvo = 'geral' OR EXISTS (SELECT 1 FROM publicacao_turma pt WHERE pt.publicacao_id = p.id AND pt.turma_id = ?))
                      ORDER BY COALESCE(p.prazo, p.data_ref, p.inicio_exibicao, p.criado_em)", [$n, $n, $turmaId])->fetchAll();
    }

    public static function registrarVisualizacao(int $id, string $origem): void
    {
        DB::q('INSERT INTO visualizacao(publicacao_id, origem, criado_em) VALUES (?,?,?)', [$id, $origem, agora()]);
    }

    /* ---------- indicadores ---------- */
    private static function contar(string $where, array $a = []): int { return (int)DB::q("SELECT COUNT(*) FROM publicacao WHERE $where", $a)->fetchColumn(); }

    public static function indicadores(): array
    {
        $n = agora();
        return [
            'ativas'    => self::contar("status='aprovado' AND (inicio_exibicao IS NULL OR inicio_exibicao<=?) AND (fim_exibicao IS NULL OR fim_exibicao>=?)", [$n, $n]),
            'revisao'   => self::contar("status='em_revisao'"),
            'agendadas' => self::contar("status='aprovado' AND inicio_exibicao>?", [$n]),
            'expiradas' => self::contar("status='aprovado' AND fim_exibicao<?", [$n]),
            'rascunhos' => self::contar("status='rascunho'"),
        ];
    }

    public static function provasETarefasProximas(string $hoje): array
    {
        return DB::q("SELECT p.titulo, p.tipo, COALESCE(p.prazo,p.data_ref) AS d,
                        (SELECT group_concat(t.nome,', ') FROM publicacao_turma pt JOIN turma t ON t.id=pt.turma_id WHERE pt.publicacao_id=p.id) AS tm
                      FROM publicacao p WHERE p.status='aprovado' AND p.tipo IN ('prova','tarefa') AND COALESCE(p.prazo,p.data_ref)>=? ORDER BY d LIMIT 5", [$hoje])->fetchAll();
    }

    public static function aguardandoRevisao(int $limite = 6): array
    {
        return DB::q("SELECT p.id, p.titulo, p.tipo, u.nome FROM publicacao p JOIN usuario u ON u.id=p.autor_id WHERE p.status='em_revisao' ORDER BY p.atualizado_em LIMIT " . (int)$limite)->fetchAll();
    }

    public static function porTipoStatus(): array
    {
        $m = [];
        foreach (DB::q('SELECT tipo, status, COUNT(*) c FROM publicacao GROUP BY tipo, status')->fetchAll() as $r) $m[$r['tipo']][$r['status']] = (int)$r['c'];
        return $m;
    }

    public static function maisConsultadas(): array
    {
        return DB::q('SELECT p.titulo, COUNT(*) c FROM visualizacao v JOIN publicacao p ON p.id=v.publicacao_id GROUP BY p.id ORDER BY c DESC LIMIT 5')->fetchAll();
    }

    public static function atividades(): array
    {
        return DB::q('SELECT r.acao, r.criado_em, p.titulo, u.nome FROM revisao_publicacao r JOIN publicacao p ON p.id=r.publicacao_id JOIN usuario u ON u.id=r.usuario_id ORDER BY r.id DESC LIMIT 5')->fetchAll();
    }
}
