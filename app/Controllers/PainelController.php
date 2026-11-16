<?php
namespace App\Controllers;

use App\Models\Evento;
use App\Models\Publicacao;
use App\Services\TvService;
use App\Services\View;

class PainelController
{
    public function resumo(): void
    {
        $hoje = date('Y-m-d');
        $linhas = [];
        foreach (Evento::proximos($hoje) as $e) $linhas[] = [$e['d'], $e['titulo'], config('app.tipos_evento.' . $e['tipo'], 'Evento')];
        foreach (Publicacao::provasETarefasProximas($hoje) as $e) $linhas[] = [$e['d'], $e['titulo'], config('app.tipos.' . $e['tipo']) . ($e['tm'] ? ' · ' . $e['tm'] : '')];
        usort($linhas, fn($a, $b) => strcmp($a[0], $b[0]));

        $tv = TvService::dados();
        View::render('painel/resumo', [
            'titulo' => 'Resumo', 'aba' => 'resumo',
            'ind' => Publicacao::indicadores(), 'naTv' => count($tv['noticias']) + count($tv['comunicados']),
            'datas' => array_slice($linhas, 0, 8), 'fila' => Publicacao::aguardandoRevisao(),
            'tabela' => Publicacao::porTipoStatus(), 'topo' => Publicacao::maisConsultadas(), 'atividades' => Publicacao::atividades(),
        ]);
    }
}
