<?php
namespace App\Controllers;

use App\Models\Evento;
use App\Models\Publicacao;
use App\Models\Turma;
use App\Services\RegrasTv;
use App\Services\Upload;
use App\Services\View;
use RuntimeException;

/* Publicações: criar, editar, enviar para revisão, aprovar, devolver, arquivar. */
class PublicacaoController
{
    public function lista(): void
    {
        $f = ['q' => trim($_GET['q'] ?? ''), 'tipo' => $_GET['tipo'] ?? '', 'status' => $_GET['status'] ?? '',
              'turma' => (int)($_GET['turma'] ?? 0), 'de' => $_GET['de'] ?? '', 'ate' => $_GET['ate'] ?? ''];
        View::render('publicacoes/lista', [
            'titulo' => 'Publicações', 'aba' => 'publicacoes', 'f' => $f,
            'rows' => Publicacao::listar($f, pode('revisor') ? null : (int)usuario()['id']),
            'turmas' => Turma::ativas(),
        ]);
    }

    public function nova(): void { $this->form(0); }
    public function editar(int $id): void { $this->form($id); }

    private function form(int $id): void
    {
        $r = ['tipo' => 'aviso', 'titulo' => '', 'descricao' => '', 'disciplina' => '', 'data_ref' => '', 'prazo' => '', 'publico_alvo' => 'geral',
              'visibilidade' => 'interno', 'exibir_tv' => 0, 'prioridade' => 1, 'duracao_seg' => 12, 'inicio_exibicao' => '', 'fim_exibicao' => '',
              'evento_id' => '', 'imagem' => ''];
        $sel = [];
        if ($id) {
            $x = Publicacao::achar($id);
            if (!$x || !$this->podeEditar($x)) { flash('err', 'Publicação não encontrada.'); redirect('painel/publicacoes'); }
            $r = array_merge($r, $x);
            $sel = Publicacao::turmasIds($id);
        }
        if (!empty($_SESSION['old'])) {   // repõe o que foi digitado depois de um erro de validação
            $o = $_SESSION['old']; unset($_SESSION['old']);
            foreach (['tipo', 'titulo', 'descricao', 'disciplina', 'data_ref', 'prazo', 'publico_alvo', 'visibilidade', 'prioridade', 'duracao_seg', 'evento_id'] as $k) if (isset($o[$k])) $r[$k] = $o[$k];
            $r['exibir_tv'] = !empty($o['exibir_tv']) ? 1 : 0;
            $r['inicio_exibicao'] = de_local($o['inicio'] ?? '') ?? '';
            $r['fim_exibicao'] = de_local($o['fim'] ?? '') ?? '';
            $sel = array_map('intval', $o['turmas'] ?? []);
        }
        View::render('publicacoes/form', [
            'titulo' => $id ? 'Editar publicação' : 'Nova publicação', 'aba' => 'publicacoes',
            'id' => $id, 'r' => $r, 'sel' => $sel, 'turmas' => Turma::ativas(), 'eventos' => Evento::opcoes(),
        ]);
    }

    private function podeEditar(array $p): bool { return pode('revisor') || (int)$p['autor_id'] === (int)usuario()['id']; }

    public function salvar(): void
    {
        $u = usuario();
        $id = (int)($_POST['id'] ?? 0);
        $velho = null;
        if ($id) {
            $velho = Publicacao::achar($id);
            if (!$velho || !$this->podeEditar($velho)) { flash('err', 'Você só edita as suas publicações.'); redirect('painel/publicacoes'); }
        }

        $tipo = $_POST['tipo'] ?? '';
        $titulo = trim($_POST['titulo'] ?? ''); $desc = trim($_POST['descricao'] ?? ''); $disc = trim($_POST['disciplina'] ?? '');
        $dataref = ($_POST['data_ref'] ?? '') ?: null; $prazo = ($_POST['prazo'] ?? '') ?: null;
        $alvo = ($_POST['publico_alvo'] ?? 'geral') === 'turmas' ? 'turmas' : 'geral';
        $turmas = array_values(array_unique(array_map('intval', $_POST['turmas'] ?? [])));
        $vis = array_key_exists($_POST['visibilidade'] ?? '', config('app.visibilidade')) ? $_POST['visibilidade'] : 'interno';
        $tv = !empty($_POST['exibir_tv']) ? 1 : 0;
        $prio = max(1, min(3, (int)($_POST['prioridade'] ?? 1)));
        $dur = max(5, min(120, (int)($_POST['duracao_seg'] ?? 12)));
        $ini = de_local($_POST['inicio'] ?? ''); $fim = de_local($_POST['fim'] ?? '');
        $evento = (int)($_POST['evento_id'] ?? 0) ?: null;

        $erros = [];
        if (!isset(config('app.tipos')[$tipo])) $erros[] = 'Escolha o tipo.';
        if ($titulo === '') $erros[] = 'Informe o título.';
        if (in_array($tipo, ['recado', 'prova', 'tarefa', 'recado_individual'], true)) $alvo = 'turmas';
        if ($alvo === 'turmas' && !$turmas) $erros[] = 'Selecione ao menos uma turma.';
        if ($alvo === 'geral') $turmas = [];
        if ($tipo === 'recado_individual') { $vis = 'privado'; $tv = 0; }
        if (in_array($tipo, ['prova', 'tarefa'], true) && $disc === '') $erros[] = 'Informe a disciplina.';
        if ($tipo === 'prova' && !$dataref) $erros[] = 'Informe a data da prova.';
        if ($tipo === 'tarefa' && !$prazo) $erros[] = 'Informe o prazo da tarefa.';
        if ($ini && $fim && $fim < $ini) $erros[] = 'O fim da exibição deve ser depois do início.';
        if ($tv) {
            if (!$fim) $erros[] = 'Conteúdo da TV precisa de uma data de validade (fim da exibição).';
            if ($m = RegrasTv::validar(['tipo' => $tipo, 'visibilidade' => $vis, 'publico_alvo' => $alvo, 'texto' => "$titulo $desc $disc"])) {
                $erros[] = 'Bloqueado para a TV pública: ' . $m;
            }
        }
        $img = '';
        try { $img = Upload::salvar('imagem', 'pub'); } catch (RuntimeException $e) { $erros[] = $e->getMessage(); }
        if ($erros) {
            Upload::apagar($img);
            $_SESSION['old'] = $_POST;
            flash('err', implode(' ', $erros));
            redirect('painel/publicacoes/' . ($id ? "$id/editar" : 'nova'));
        }

        $destino = $_POST['destino'] ?? 'rascunho';
        $status = ($destino === 'aprovar' && pode('revisor')) ? 'aprovado' : ($destino === 'revisao' ? 'em_revisao' : 'rascunho');
        if ($img && $velho) Upload::apagar($velho['imagem']);

        $id = Publicacao::salvar($id ?: null, [
            'tipo' => $tipo, 'titulo' => $titulo, 'descricao' => $desc, 'disciplina' => $disc ?: null, 'data_ref' => $dataref, 'prazo' => $prazo,
            'imagem' => $img ?: ($velho['imagem'] ?? null), 'autor_id' => $u['id'], 'evento_id' => $evento, 'publico_alvo' => $alvo, 'visibilidade' => $vis,
            'exibir_tv' => $tv, 'status' => $status, 'prioridade' => $prio, 'duracao_seg' => $dur, 'inicio_exibicao' => $ini, 'fim_exibicao' => $fim,
        ], $turmas);

        Publicacao::registrarRevisao($id, $u['id'], $velho ? 'editada' : 'criada');
        if ($status === 'em_revisao') Publicacao::registrarRevisao($id, $u['id'], 'enviada');
        if ($status === 'aprovado') Publicacao::registrarRevisao($id, $u['id'], 'aprovada');
        $aviso = ($velho && $velho['status'] === 'aprovado' && $status !== 'aprovado') ? ' Como foi alterada, precisa ser revisada de novo antes de voltar a ser exibida.' : '';
        flash('ok', 'Publicação salva: ' . config('app.status.' . $status) . '.' . $aviso);
        redirect('painel/publicacoes');
    }

    /* ---------- fluxo de revisão ---------- */
    public function enviar(int $id): void   { $this->mudar('enviar', $id); }
    public function arquivar(int $id): void { $this->mudar('arquivar', $id); }
    public function aprovar(int $id): void  { $this->mudar('aprovar', $id); }
    public function rejeitar(int $id): void { $this->mudar('rejeitar', $id); }

    private function mudar(string $acao, int $id): void
    {
        $u = usuario();
        $volta = ($_POST['voltar'] ?? '') === 'revisao' ? 'painel/revisao' : 'painel/publicacoes';
        $com = trim($_POST['comentario'] ?? '');
        $p = Publicacao::achar($id);
        if (!$p) { flash('err', 'Publicação não encontrada.'); redirect($volta); }
        $dono = (int)$p['autor_id'] === (int)$u['id'];
        $novo = null; $log = null; $msg = '';

        if ($acao === 'enviar' && ($dono || pode('revisor')) && in_array($p['status'], ['rascunho', 'rejeitado'], true)) {
            $novo = 'em_revisao'; $log = 'enviada'; $msg = 'enviada para revisão';
        } elseif ($acao === 'aprovar' && pode('revisor') && $p['status'] === 'em_revisao') {
            if ($p['exibir_tv'] && ($m = RegrasTv::validarPublicacao($p))) { flash('err', 'Não aprovada: ' . $m); redirect($volta); }
            $novo = 'aprovado'; $log = 'aprovada'; $msg = 'aprovada';
        } elseif ($acao === 'rejeitar' && pode('revisor') && $p['status'] === 'em_revisao') {
            if ($com === '') { flash('err', 'Explique o motivo da devolução no comentário.'); redirect($volta); }
            $novo = 'rejeitado'; $log = 'rejeitada'; $msg = 'devolvida ao autor';
        } elseif ($acao === 'arquivar' && ($dono || pode('revisor')) && $p['status'] !== 'arquivado') {
            $novo = 'arquivado'; $log = 'arquivada'; $msg = 'arquivada (continua no histórico)';
        }
        if (!$novo) { flash('err', 'Ação não permitida para este perfil ou status.'); redirect($volta); }

        Publicacao::mudarStatus($id, $novo);
        Publicacao::registrarRevisao($id, $u['id'], $log, $com);
        flash('ok', "Publicação $msg.");
        redirect($volta);
    }

    /* ---------- fila de revisão ---------- */
    public function fila(): void
    {
        $rows = Publicacao::fila();
        foreach ($rows as &$r) {
            $r['bloqueio'] = $r['exibir_tv'] ? RegrasTv::validarPublicacao($r) : null;
            $r['historico'] = Publicacao::historico((int)$r['id']);
        }
        unset($r);
        View::render('publicacoes/fila', ['titulo' => 'Fila de revisão', 'aba' => 'revisao', 'rows' => $rows]);
    }
}
