<?php
namespace App\Controllers;

use App\Models\Turma;
use App\Services\View;

class TurmaController
{
    public function index(): void
    {
        View::render('turmas/index', ['titulo' => 'Turmas', 'aba' => 'turmas', 'turmas' => Turma::todasComContagem()]);
    }

    public function criar(): void
    {
        $nome = trim($_POST['nome'] ?? '');
        if ($nome === '') flash('err', 'Informe o nome da turma.');
        elseif (Turma::existe($nome)) flash('err', 'Essa turma já existe.');
        else { Turma::criar($nome); flash('ok', 'Turma criada.'); }
        redirect('painel/turmas');
    }

    public function alternar(int $id): void
    {
        Turma::alternar($id);
        flash('ok', 'Turma atualizada.');
        redirect('painel/turmas');
    }
}
