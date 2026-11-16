<?php
namespace App\Controllers;

use App\Models\Aniversariante;
use App\Models\Turma;
use App\Services\ImportadorAniversariantes;
use App\Services\Upload;
use App\Services\View;
use RuntimeException;

/* Aniversariantes: só aparecem na TV depois que o administrador registra a autorização. */
class AniversarianteController
{
    public function index(): void
    {
        View::render('aniversariantes/index', [
            'titulo' => 'Aniversariantes', 'aba' => 'aniversariantes', 'turmas' => Turma::ativas(), 'rows' => Aniversariante::todos(),
            'meses' => ['', 'Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho', 'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro'],
        ]);
    }

    public function criar(): void
    {
        $nome = trim($_POST['nome'] ?? ''); $dn = $_POST['data_nascimento'] ?? '';
        if ($nome === '' || !strtotime($dn)) { flash('err', 'Informe o nome e a data de nascimento.'); redirect('painel/aniversariantes'); }
        try { $foto = Upload::salvar('foto', 'aniv'); } catch (RuntimeException $e) { flash('err', $e->getMessage()); redirect('painel/aniversariantes'); }
        Aniversariante::criar($nome, (int)($_POST['turma_id'] ?? 0) ?: null, $dn, $foto ?: null);
        flash('ok', 'Cadastrado. Ele só aparece na TV depois que um administrador registrar a autorização.');
        redirect('painel/aniversariantes');
    }

    public function importar(): void
    {
        try {
            [$ok, $ign] = ImportadorAniversariantes::importar($_FILES['csv'] ?? null);
            flash('ok', "Importação concluída: $ok importado(s), $ign ignorado(s). Nenhum foi autorizado automaticamente.");
        } catch (RuntimeException $e) {
            flash('err', $e->getMessage());
        }
        redirect('painel/aniversariantes');
    }

    public function autorizar(): void
    {
        $ids = array_map('intval', $_POST['sel'] ?? []);
        if (!$ids) flash('err', 'Marque ao menos um aniversariante.');
        elseif (empty($_POST['politica'])) flash('err', 'Confirme que a autorização segue a política institucional da escola.');
        else {
            Aniversariante::definirAutorizacao($ids, true, 'Autorizado por ' . usuario()['nome'] . ' em ' . date('d/m/Y H:i'));
            flash('ok', count($ids) . ' autorizado(s) para exibição.');
        }
        redirect('painel/aniversariantes');
    }

    public function revogar(): void
    {
        $ids = array_map('intval', $_POST['sel'] ?? []);
        if (!$ids) flash('err', 'Marque ao menos um aniversariante.');
        else { Aniversariante::definirAutorizacao($ids, false, null); flash('ok', count($ids) . ' com autorização revogada.'); }
        redirect('painel/aniversariantes');
    }

    public function excluir(int $id): void
    {
        $a = Aniversariante::achar($id);
        if ($a) { Upload::apagar($a['foto']); Aniversariante::excluir($id); flash('ok', 'Removido.'); }
        redirect('painel/aniversariantes');
    }
}
