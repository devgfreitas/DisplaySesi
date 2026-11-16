<?php
namespace App\Controllers;

use App\Models\Cardapio;
use App\Services\Upload;
use App\Services\View;
use RuntimeException;

class CardapioController
{
    /* ---------- painel ---------- */
    public function lista(): void
    {
        View::render('cardapio/lista', ['titulo' => 'Cardápio semanal', 'aba' => 'cardapio', 'cardapios' => Cardapio::todos()]);
    }

    public function nova(): void
    {
        $segunda = date('Y-m-d', strtotime('monday this week', strtotime($_POST['semana'] ?? 'today') ?: time()));
        redirect('painel/cardapio/' . Cardapio::criarSemana($segunda, (int)usuario()['id']));
    }

    public function editar(int $id): void
    {
        $c = Cardapio::achar($id);
        if (!$c) { flash('err', 'Cardápio não encontrado.'); redirect('painel/cardapio'); }
        View::render('cardapio/form', ['titulo' => 'Cardápio semanal', 'aba' => 'cardapio', 'c' => $c, 'g' => Cardapio::grade($id)]);
    }

    public function salvar(int $id): void
    {
        $c = Cardapio::achar($id);
        if (!$c) { flash('err', 'Cardápio não encontrado.'); redirect('painel/cardapio'); }
        $img = '';
        try { $img = Upload::salvar('imagem', 'cardapio'); } catch (RuntimeException $e) { flash('err', $e->getMessage()); redirect("painel/cardapio/$id"); }
        if ($img) Upload::apagar($c['imagem']);

        $status = $c['status'];
        if (($_POST['acao'] ?? '') === 'publicar' && pode('revisor')) $status = 'publicado';
        elseif (!pode('revisor') && $status === 'publicado') $status = 'rascunho';    // alteração feita por editor volta para revisão

        Cardapio::salvar($id, trim($_POST['titulo'] ?? '') ?: 'Cardápio da semana', $img ?: null, $status, $_POST['c'] ?? []);
        flash('ok', $status === 'publicado' ? 'Cardápio salvo e publicado.'
            : 'Cardápio salvo como rascunho' . (($_POST['acao'] ?? '') === 'publicar' ? ' (apenas revisor ou administrador publica).' : '.'));
        redirect("painel/cardapio/$id");
    }

    public function despublicar(int $id): void
    {
        Cardapio::definirStatus($id, 'rascunho');
        flash('ok', 'Cardápio despublicado.');
        redirect("painel/cardapio/$id");
    }

    public function excluir(int $id): void
    {
        $c = Cardapio::achar($id);
        if ($c) { Upload::apagar($c['imagem']); Cardapio::excluir($id); flash('ok', 'Cardápio excluído.'); }
        redirect('painel/cardapio');
    }

    /* ---------- visualização pública / impressão ---------- */
    public function publico(int $id = 0): void
    {
        if ($id) {
            $c = Cardapio::achar($id);
            if ($c && $c['status'] !== 'publicado' && !usuario()) $c = null;    // rascunho só a equipe vê
        } else {
            $c = Cardapio::ultimoPublicadoAte(date('Y-m-d'));
        }
        View::render('cardapio/publico', [
            'titulo' => 'Cardápio semanal', 'c' => $c, 'g' => $c ? Cardapio::grade((int)$c['id']) : [], 'outras' => Cardapio::publicados(),
        ], 'publico');
    }
}
