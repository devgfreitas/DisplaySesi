<?php
namespace App\Services;

use App\Models\Aniversariante;
use App\Models\Cardapio;
use App\Models\Evento;
use App\Models\Publicacao;

/*
 * Monta tudo o que a TV pública pode exibir. É o ponto único de saída de dados para a TV:
 * só passa o que é público, geral, aprovado, dentro da validade e sem dados sensíveis.
 */
class TvService
{
    public static function dados(): array
    {
        $o = ['noticias' => [], 'comunicados' => [], 'eventos' => [], 'cardapio' => [], 'aniversarios' => []];

        foreach (Publicacao::paraTv() as $r) {
            if (RegrasTv::conteudoSensivel($r['titulo'] . ' ' . $r['descricao'] . ' ' . $r['disciplina'])) continue;   // segunda barreira
            $item = ['titulo' => $r['titulo'], 'texto' => $r['descricao'], 'img' => $r['imagem'],
                     'criado' => $r['inicio_exibicao'] ?: $r['criado_em'], 'dur' => (int)$r['idur']];
            if ($r['tipo'] === 'noticia') $o['noticias'][] = $item; else $o['comunicados'][] = $item;
        }

        foreach (Evento::paraTv() as $e) {
            $o['eventos'][] = ['titulo' => $e['titulo'], 'texto' => $e['descricao'], 'data' => $e['data_inicio'], 'img' => $e['imagem']];
        }

        $o['cardapio'] = self::cardapio();

        foreach (Aniversariante::paraTv() as $a) {
            $o['aniversarios'][] = ['titulo' => $a['nome'], 'data' => $a['data_nascimento'], 'img' => $a['foto']];
        }
        return $o;
    }

    /* identifica mudanças de conteúdo ou de validade: a TV recarrega quando muda */
    public static function versao(): string { return md5(json_encode(self::dados())); }

    private static function cardapio(): array
    {
        $c = Cardapio::atualParaTv(date('Y-m-d'));
        if (!$c) return [];
        $g = Cardapio::grade((int)$c['id']);
        $h = (int)date('G');
        $per = $h < 12 ? 'manha' : ($h < 18 ? 'tarde' : 'noite');   // período atual
        $curto = []; $comp = [];
        foreach ([1 => 'Seg', 2 => 'Ter', 3 => 'Qua', 4 => 'Qui', 5 => 'Sex'] as $d => $ab) {
            $row = $g[$d] ?? [];
            if (!array_filter($row)) continue;
            $v = $row[$per] ?? '';
            if ($v === '') foreach (array_keys(config('app.refeicoes')) as $k) if (!empty($row[$k])) { $v = $row[$k]; break; }
            $curto[] = "$ab - $v";
            $partes = [];
            foreach (config('app.refeicoes') as $k => $rot) if (!empty($row[$k])) $partes[] = $rot . ': ' . $row[$k];
            $comp[] = "$ab - " . implode(' · ', $partes);
        }
        if (!$curto) return [];
        return ['titulo' => $c['titulo'] ?: 'Cardápio da semana', 'img' => $c['imagem'], 'periodo' => config('app.refeicoes.' . $per),
                'texto' => implode("\n", $curto), 'completo' => implode("\n", $comp)];
    }
}
