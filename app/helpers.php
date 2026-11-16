<?php
/* Funções auxiliares usadas em controllers e views. */

use App\Services\Auth;
use App\Services\Csrf;

function config(string $chave, $padrao = null)
{
    static $cache = [];
    $partes = explode('.', $chave);
    $arquivo = array_shift($partes);
    if (!isset($cache[$arquivo])) {
        $f = BASE_PATH . '/config/' . $arquivo . '.php';
        $cache[$arquivo] = is_file($f) ? require $f : [];
    }
    $v = $cache[$arquivo];
    foreach ($partes as $p) {
        if (!is_array($v) || !array_key_exists($p, $v)) return $padrao;
        $v = $v[$p];
    }
    return $v;
}

/* ---------- texto, datas, URLs ---------- */
function h($s): string { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
function e($s): string { return h($s); }
function agora(): string { return date('Y-m-d H:i:s'); }
function d_br($s): string { $t = strtotime((string)$s); return $t ? date('d/m/Y', $t) : ''; }
function dh_br($s): string { $t = strtotime((string)$s); return $t ? date('d/m/Y H:i', $t) : ''; }
function dm($s): string { $t = strtotime((string)$s); return $t ? date('d/m', $t) : ''; }
function dt_local($s): string { return $s ? str_replace(' ', 'T', substr($s, 0, 16)) : ''; }
function de_local($s): ?string
{
    $s = trim((string)$s);
    if ($s === '') return null;
    $s = str_replace('T', ' ', $s);
    return strlen($s) === 16 ? $s . ':00' : $s;
}

function base_url(): string
{
    static $b = null;
    if ($b === null) $b = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '')), '/');
    return $b;
}
function url(string $caminho = ''): string { return base_url() . '/' . ltrim($caminho, '/'); }
function asset(string $caminho): string { return url('assets/' . ltrim($caminho, '/')); }
function upload_url($nome): string { return $nome ? url('uploads/' . rawurlencode($nome)) : ''; }
function redirect(string $caminho): void { header('Location: ' . url($caminho)); exit; }

/* estilo inline apenas para imagens vindas do banco (a URL muda a cada registro) */
function bg($nome, string $extra = ''): string
{
    $css = $extra . ($nome ? ($extra ? ';' : '') . "background-image:url('" . upload_url($nome) . "')" : '');
    return $css ? 'style="' . h($css) . '"' : '';
}

/* ---------- sessão, mensagens e segurança ---------- */
function flash(string $tipo, string $msg): void { $_SESSION['flash'] = [$tipo, $msg]; }
function flash_pegar(): ?array { $f = $_SESSION['flash'] ?? null; unset($_SESSION['flash']); return $f; }
function csrf_campo(): string { return Csrf::campo(); }
function usuario(): ?array { return Auth::usuario(); }
function pode(string ...$perfis): bool { return Auth::pode(...$perfis); }
function registrar_log(string $msg): void
{
    $dir = BASE_PATH . '/storage/logs';
    if (!is_dir($dir)) @mkdir($dir, 0755, true);
    @file_put_contents($dir . '/app.log', '[' . agora() . '] ' . $msg . PHP_EOL, FILE_APPEND | LOCK_EX);
}

/* ---------- componentes de formulário ---------- */
function opts(array $a, $sel = ''): string
{
    $o = '';
    foreach ($a as $k => $v) $o .= '<option value="' . h($k) . '"' . ((string)$k === (string)$sel ? ' selected' : '') . '>' . h($v) . '</option>';
    return $o;
}

/* ---------- estado de exibição de uma publicação ---------- */
function estado(array $p): array
{
    if ($p['status'] !== 'aprovado') return [$p['status'], config('app.status.' . $p['status'], $p['status'])];
    $n = agora();
    if (!empty($p['inicio_exibicao']) && $p['inicio_exibicao'] > $n) return ['agendado', 'Agendado'];
    if (!empty($p['fim_exibicao']) && $p['fim_exibicao'] < $n) return ['expirado', 'Expirado'];
    return ['no_ar', 'No ar'];
}
function badge(array $p): string { [$c, $r] = estado($p); return '<span class="b b-' . h($c) . '">' . h($r) . '</span>'; }

/* ---------- componentes da TV ---------- */
function calendario(int $ano, int $mes, array $marcados, int $hoje): string
{
    $h = '<div class="cal">';
    foreach (['Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sáb', 'Dom'] as $x) $h .= '<div class="cw">' . $x . '</div>';
    $prim = mktime(0, 0, 0, $mes, 1, $ano);
    $off = (int)date('N', $prim) - 1;
    $n = (int)date('t', $prim);
    $ant = (int)date('t', mktime(0, 0, 0, $mes - 1, 1, $ano));
    $total = (int)(ceil(($off + $n) / 7) * 7);
    for ($c = 0; $c < $total; $c++) {
        $dia = $c - $off + 1;
        $cls = '';
        if ($dia < 1)      { $cls = 'out'; $t = $ant + $dia; }
        elseif ($dia > $n) { $cls = 'out'; $t = $dia - $n; }
        else { $t = $dia; if (isset($marcados[$dia])) $cls .= ' ev'; if ($dia === $hoje) $cls .= ' hoje'; }
        $h .= '<div class="cell ' . $cls . '"><span>' . $t . '</span></div>';
    }
    return $h . '</div>';
}

/* "Seg - arroz, feijão" vira [rótulo do dia, texto] */
function linhasCardapio($txt): array
{
    $out = [];
    foreach (preg_split('/\R/u', trim((string)$txt)) as $l) {
        $l = trim($l);
        if ($l === '') continue;
        if (preg_match('/^([^\-–:]{2,14}?)\s*[-–:]\s*(.+)$/u', $l, $m)) $out[] = [trim($m[1]), trim($m[2])];
        else $out[] = ['', $l];
    }
    return $out;
}

/* duração (ms) de um grupo de cards da TV: a maior duração individual, ou o tempo padrão */
function durGrupo(array $grupo): int
{
    $m = 0;
    foreach ($grupo as $x) $m = max($m, (int)($x['dur'] ?? 0));
    return ($m ?: (int)config('app.tv.tempo_slide')) * 1000;
}

/* botão que envia um POST pequeno (enviar, arquivar, remover...) com CSRF; data-confirm é tratado por painel.js */
function form_acao(string $rota, string $rotulo, string $classe = '', string $confirma = '', array $extra = []): string
{
    $f = '<form class="in" method="post" action="' . h(url($rota)) . '"' . ($confirma !== '' ? ' data-confirm="' . h($confirma) . '"' : '') . '>' . csrf_campo();
    foreach ($extra as $k => $v) $f .= '<input type="hidden" name="' . h($k) . '" value="' . h($v) . '">';
    return $f . '<button class="p ' . h($classe) . '">' . h($rotulo) . '</button></form>';
}
