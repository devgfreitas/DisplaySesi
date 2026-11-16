<?php
namespace App\Controllers;

use App\Models\Evento;
use App\Services\Upload;
use App\Services\View;
use RuntimeException;

class EventoController
{
    public function index(int $id = 0): void
    {
        $e = ['titulo' => '', 'descricao' => '', 'tipo' => 'evento', 'data_inicio' => '', 'data_fim' => '', 'local' => '', 'publico' => 1];
        if ($id && ($x = Evento::achar($id))) $e = $x; else $id = 0;
        View::render('eventos/index', ['titulo' => 'Calendário escolar', 'aba' => 'eventos', 'id' => $id, 'e' => $e, 'eventos' => Evento::todos()]);
    }

    public function salvar(): void
    {
        $eid = (int)($_POST['id'] ?? 0);
        $volta = 'painel/eventos' . ($eid ? "/$eid/editar" : '');
        $titulo = trim($_POST['titulo'] ?? ''); $ini = $_POST['data_inicio'] ?? ''; $fim = ($_POST['data_fim'] ?? '') ?: null;
        if ($titulo === '' || !strtotime($ini)) { flash('err', 'Informe o título e a data do evento.'); redirect($volta); }
        if ($fim && $fim < $ini) { flash('err', 'A data final deve ser igual ou posterior à inicial.'); redirect($volta); }
        try { $img = Upload::salvar('imagem', 'evento'); } catch (RuntimeException $e) { flash('err', $e->getMessage()); redirect($volta); }
        if ($img && $eid && ($old = Evento::achar($eid))) Upload::apagar($old['imagem']);

        Evento::salvar($eid ?: null, [
            'titulo' => $titulo, 'descricao' => trim($_POST['descricao'] ?? ''), 'data_inicio' => $ini, 'data_fim' => $fim,
            'tipo' => array_key_exists($_POST['tipo'] ?? '', config('app.tipos_evento')) ? $_POST['tipo'] : 'evento',
            'local' => trim($_POST['local'] ?? ''), 'publico' => !empty($_POST['publico']) ? 1 : 0,
        ], $img ?: null, (int)usuario()['id']);
        flash('ok', 'Evento salvo.');
        redirect('painel/eventos');
    }

    public function excluir(int $id): void
    {
        $e = Evento::achar($id);
        if ($e) { Upload::apagar($e['imagem']); Evento::excluir($id); flash('ok', 'Evento excluído.'); }
        redirect('painel/eventos');
    }
}
