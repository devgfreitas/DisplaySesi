<?php
namespace App\Controllers;

use App\Models\Evento;
use App\Models\Publicacao;
use App\Models\Turma;
use App\Services\View;

/* Área de consulta por turma e perfil. Recados individuais e conteúdo privado nunca aparecem aqui. */
class ConsultaController
{
    public function index(): void
    {
        $turmas = Turma::ativas();
        $tid = (int)($_GET['turma'] ?? 0);
        if (!isset($turmas[$tid])) $tid = 0;
        $perfil = ($_GET['perfil'] ?? 'visitante') === 'comunidade' ? 'comunidade' : 'visitante';

        $grupos = [];
        if ($tid) {
            foreach (Publicacao::paraConsulta($tid, $perfil) as $r) {
                $grupos[$r['tipo']][] = $r;
                Publicacao::registrarVisualizacao((int)$r['id'], 'consulta');
            }
        }
        View::render('consulta/index', [
            'titulo' => 'Área de consulta', 'turmas' => $turmas, 'tid' => $tid, 'perfil' => $perfil, 'grupos' => $grupos,
            'ordem' => ['prova' => 'Provas', 'tarefa' => 'Tarefas', 'recado' => 'Recados da turma', 'comunicado' => 'Comunicados', 'aviso' => 'Avisos', 'noticia' => 'Notícias'],
            'eventos' => array_slice(array_filter(Evento::paraTv(), fn($e) => $e['data_inicio'] >= date('Y-m-d')), 0, 5),
        ], 'publico');
    }
}
