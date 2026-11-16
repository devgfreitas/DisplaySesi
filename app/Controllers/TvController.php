<?php
namespace App\Controllers;

use App\Services\TvService;
use App\Services\View;

/* TV da entrada: tela pública, sem partes clicáveis, em rotação automática. */
class TvController
{
    public function index(): void
    {
        $tv = TvService::dados();
        $erro = flash_pegar();

        $eventos = $tv['eventos'];
        usort($eventos, fn($a, $b) => strcmp($a['data'] ?? '', $b['data'] ?? ''));
        $ano = (int)date('Y'); $mes = (int)date('n');
        $marcados = [];
        foreach ($eventos as $ev) {
            $t = strtotime($ev['data'] ?? '');
            if ($t && (int)date('Y', $t) === $ano && (int)date('n', $t) === $mes) $marcados[(int)date('j', $t)] = true;
        }
        $hojeTs = strtotime('today');
        $proximos = array_values(array_filter($eventos, fn($x) => strtotime($x['data'] ?? '') >= $hojeTs));
        if (!$proximos) $proximos = $eventos;

        $aniversarios = $tv['aniversarios'];
        usort($aniversarios, function ($x, $y) {
            $a = strtotime($x['data'] ?? ''); $b = strtotime($y['data'] ?? '');
            return ($a ? (int)date('j', $a) : 99) <=> ($b ? (int)date('j', $b) : 99);
        });
        $paginasAniv = array_chunk($aniversarios, 4);
        $cfg = config('app.tv');
        $noticias = array_reverse($tv['noticias']);        // a view inverte de novo: o card de maior prioridade vem primeiro
        $comunicados = array_reverse($tv['comunicados']);

        View::render('tv/index', [
            'adm' => (bool)usuario(), 'loginAberto' => $erro || isset($_GET['login']), 'erro' => $erro,
            'noticias' => $noticias, 'comunicados' => $comunicados, 'eventos' => $eventos, 'cardapio' => $tv['cardapio'],
            'aniversarios' => $aniversarios, 'paginasAniv' => $paginasAniv,
            'durFesta' => min($cfg['max_festa_seg'] * 1000, max($cfg['tempo_slide'] * 1000, count($paginasAniv) * $cfg['ms_carrossel_festa'])),
            'msHome' => $cfg['ms_carrossel_home'], 'msFesta' => $cfg['ms_carrossel_festa'],
            'tempoSlide' => $cfg['tempo_slide'], 'pollingSeg' => $cfg['polling_seg'],
            'cores' => ['#E30613', '#171717', '#ffc400', '#ff6b81', '#b3000f'],
            'MES' => ['', 'Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho', 'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro'],
            'MESC' => ['', 'Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun', 'Jul', 'Ago', 'Set', 'Out', 'Nov', 'Dez'],
            'ano' => $ano, 'mes' => $mes, 'hojeDia' => (int)date('j'), 'marcados' => $marcados, 'proximos' => array_slice($proximos, 0, 4),
            'ic' => self::icones(),
            'menu' => [['home', 'Início'], ['mail', 'Comunicados'], ['news', 'Notícias'], ['tv', 'TV Escolar'], ['cal', 'Calendário'], ['food', 'Cardápio'], ['cake', 'Aniversariantes'], ['proj', 'Projetos'], ['info', 'Sobre o Sesi']],
            'heroImg' => $noticias ? end($noticias)['img'] : '',
            'ultCom' => $comunicados ? end($comunicados) : null,
            'versao' => md5(json_encode($tv)),
        ], null);
    }

    /* a TV consulta esta rota para saber se algo mudou (novo card, expiração, etc.) */
    public function versao(): void
    {
        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-store');
        echo json_encode(['v' => TvService::versao()]);
    }

    private static function icones(): array
    {
        return [
            'home' => '<path d="M3 11l9-8 9 8M5 10v10h14V10"/>', 'mail' => '<path d="M3 6h18v12H3zM3 7l9 7 9-7"/>',
            'news' => '<path d="M5 4h14v16H5zM8 8h8M8 12h8M8 16h5"/>', 'tv' => '<path d="M3 6h18v12H3zM8 21h8"/>',
            'cal' => '<path d="M4 6h16v14H4zM4 10h16M8 3v4M16 3v4"/>', 'food' => '<path d="M7 3v8M5 3v5a2 2 0 004 0V3M7 11v10M17 3c-2 2-2 6 0 8v10"/>',
            'cake' => '<path d="M4 13h16v7H4zM4 16c3 2 5-2 8 0s5-2 8 0M12 13V9M12 5v1"/>', 'proj' => '<path d="M12 3l8 3v6c0 5-4 8-8 9-4-1-8-4-8-9V6z"/>',
            'info' => '<circle cx="12" cy="12" r="9"/><path d="M12 11v5M12 8v.5"/>',
        ];
    }
}
