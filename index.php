<?php
session_start();

/* ====== CONFIGURAÇÃO ====== */
const SENHA_ADM   = 'sesi123';   // TROQUE A SENHA
const TEMPO_SLIDE = 14;          // segundos por tela
const MS_CARROSSEL_HOME = 4000;   // tempo de cada página do carrossel (início)
const MS_CARROSSEL_FESTA = 6000;  // tempo de cada página na tela de aniversariantes
const MAX_FESTA_SEG      = 120;   // tempo máximo dessa tela por ciclo (retoma de onde parou no ciclo seguinte)
const ARQ_DADOS   = __DIR__ . '/data.json';

$erro = '';
if (isset($_GET['sair'])) { session_destroy(); header('Location: index.php'); exit; }
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['senha'])) {
    if (hash_equals(SENHA_ADM, $_POST['senha'])) { $_SESSION['adm'] = true; header('Location: index.php'); exit; }
    $erro = 'Senha incorreta.';
}
$adm = !empty($_SESSION['adm']);
$loginAberto = $erro || isset($_GET['login']);

$d = is_file(ARQ_DADOS) ? (json_decode(file_get_contents(ARQ_DADOS), true) ?: []) : [];
$noticias     = $d['noticia']        ?? [];
$cardapio     = $d['cardapio']       ?? [];
$eventos      = $d['evento']         ?? [];
$comunicados  = $d['comunicado']     ?? [];
$aniversarios = $d['aniversariante'] ?? [];

function e($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
function img($s) { return $s ? 'uploads/' . rawurlencode($s) : ''; }
function bg($s, $extra = '') {
    $css = $extra . ($s ? ($extra ? ';' : '') . "background-image:url('" . img($s) . "')" : '');
    return $css ? 'style="' . $css . '"' : '';
}
function dm($s) { $t = strtotime($s ?? ''); return $t ? date('d/m', $t) : ''; }

$MES  = ['','Janeiro','Fevereiro','Março','Abril','Maio','Junho','Julho','Agosto','Setembro','Outubro','Novembro','Dezembro'];
$MESC = ['','Jan','Fev','Mar','Abr','Mai','Jun','Jul','Ago','Set','Out','Nov','Dez'];
$hojeTs = strtotime('today');
$ano = (int)date('Y'); $mes = (int)date('n'); $hojeDia = (int)date('j');

/* eventos ordenados + marcados no mês atual */
usort($eventos, fn($a, $b) => strcmp($a['data'] ?? '', $b['data'] ?? ''));
$marcados = [];
foreach ($eventos as $ev) {
    $t = strtotime($ev['data'] ?? '');
    if ($t && (int)date('Y', $t) === $ano && (int)date('n', $t) === $mes) $marcados[(int)date('j', $t)] = true;
}
$proximos = array_values(array_filter($eventos, fn($x) => strtotime($x['data'] ?? '') >= $hojeTs));
if (!$proximos) $proximos = $eventos;
$proximos = array_slice($proximos, 0, 4);

function calendario($ano, $mes, $marcados, $hoje) {
    $h = '<div class="cal">';
    foreach (['Seg','Ter','Qua','Qui','Sex','Sáb','Dom'] as $x) $h .= '<div class="cw">' . $x . '</div>';
    $prim = mktime(0,0,0,$mes,1,$ano);
    $off = (int)date('N', $prim) - 1; $n = (int)date('t', $prim);
    $ant = (int)date('t', mktime(0,0,0,$mes-1,1,$ano));
    $total = (int)(ceil(($off + $n) / 7) * 7);
    for ($c = 0; $c < $total; $c++) {
        $dia = $c - $off + 1; $cls = '';
        if ($dia < 1)       { $cls = 'out'; $t = $ant + $dia; }
        elseif ($dia > $n)  { $cls = 'out'; $t = $dia - $n; }
        else { $t = $dia; if (isset($marcados[$dia])) $cls .= ' ev'; if ($dia === $hoje) $cls .= ' hoje'; }
        $h .= '<div class="cell ' . $cls . '"><span>' . $t . '</span></div>';
    }
    return $h . '</div>';
}
/* cardápio: "Seg - arroz, feijão..." vira linhas com dia em destaque */
function linhasCardapio($txt) {
    $out = [];
    foreach (preg_split('/\R/u', trim((string)$txt)) as $l) {
        $l = trim($l); if ($l === '') continue;
        if (preg_match('/^([^\-–:]{2,14}?)\s*[-–:]\s*(.+)$/u', $l, $m)) $out[] = [trim($m[1]), trim($m[2])];
        else $out[] = ['', $l];
    }
    return $out;
}
$ic = [
 'home' => '<path d="M3 11l9-8 9 8M5 10v10h14V10"/>',
 'mail' => '<path d="M3 6h18v12H3zM3 7l9 7 9-7"/>',
 'news' => '<path d="M5 4h14v16H5zM8 8h8M8 12h8M8 16h5"/>',
 'tv'   => '<path d="M3 6h18v12H3zM8 21h8"/>',
 'cal'  => '<path d="M4 6h16v14H4zM4 10h16M8 3v4M16 3v4"/>',
 'food' => '<path d="M7 3v8M5 3v5a2 2 0 004 0V3M7 11v10M17 3c-2 2-2 6 0 8v10"/>',
 'cake' => '<path d="M4 13h16v7H4zM4 16c3 2 5-2 8 0s5-2 8 0M12 13V9M12 5v1"/>',
 'proj' => '<path d="M12 3l8 3v6c0 5-4 8-8 9-4-1-8-4-8-9V6z"/>',
 'info' => '<circle cx="12" cy="12" r="9"/><path d="M12 11v5M12 8v.5"/>',
];
$menu = [['home','Início'],['mail','Comunicados'],['news','Notícias'],['tv','TV Escolar'],['cal','Calendário'],['food','Cardápio'],['cake','Aniversariantes'],['proj','Projetos'],['info','Sobre o Sesi']];
$heroImg = $noticias ? end($noticias)['img'] : '';
$ultCom  = $comunicados ? end($comunicados) : null;
usort($aniversarios, function ($x, $y) {
    $a = strtotime($x['data'] ?? ''); $b = strtotime($y['data'] ?? '');
    return ($a ? (int)date('j', $a) : 99) <=> ($b ? (int)date('j', $b) : 99);
});
$paginasAniv = array_chunk($aniversarios, 4);
$durFesta = min(MAX_FESTA_SEG * 1000, max(TEMPO_SLIDE * 1000, count($paginasAniv) * MS_CARROSSEL_FESTA));
$cores = ['#E30613', '#171717', '#ffc400', '#ff6b81', '#b3000f'];
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Sesi</title>
<style>
:root{--verm:#E30613;--vermesc:#b3000f;--rosa:#fde3e5;--branco:#FFFFFF;--preto:#171717;--cinza:#F2F2F2}
*{box-sizing:border-box;margin:0;padding:0}
html,body{height:100%;overflow:hidden}
body{font-family:Arial,Helvetica,sans-serif;background:var(--cinza);color:var(--preto);cursor:none;user-select:none;display:flex;flex-direction:column}
body.adm-ativo,body.login-aberto{cursor:default}
svg.i{width:2.6vh;height:2.6vh;fill:none;stroke:currentColor;stroke-width:2;stroke-linecap:round;stroke-linejoin:round;flex:none}

/* cabeçalho */
header{height:10.5vh;background:var(--verm);color:var(--branco);display:flex;align-items:center;padding:0 2vw;gap:1.6vw;flex:none}
.logo{background:var(--branco);color:var(--verm);font-weight:900;font-style:italic;font-size:5vh;padding:.5vh 1.6vw;border-radius:1.2vh;letter-spacing:.1vh;line-height:1.1}
.sep{width:2px;height:6vh;background:rgba(255,255,255,.7)}
.nome b{display:block;font-size:4vh;line-height:1}
.nome span{font-size:1.7vh;font-weight:700}
.rel{margin-left:auto;background:var(--branco);color:var(--preto);border-radius:5vh;padding:1.2vh 2vw;display:flex;align-items:baseline;gap:1.2vw}
.rel b{font-size:3.4vh;color:var(--verm)}.rel span{font-size:2vh}

.corpo{flex:1;display:flex;min-height:0}
/* menu lateral (somente visual) */
nav{width:16vw;padding:2vh 1vw;display:flex;flex-direction:column;gap:.8vh;flex:none}
nav .it{display:flex;align-items:center;gap:.9vw;padding:1.3vh .9vw;border-radius:1.2vh;font-size:2vh;color:#333;transition:.3s}
nav .it.on{background:var(--verm);color:var(--branco);font-weight:700}
nav .marca{margin-top:auto;background:var(--verm);color:var(--branco);border-radius:1.6vh;padding:2.4vh 1vw;font-weight:900;font-style:italic;font-size:4vh;line-height:1.1;position:relative;overflow:hidden}
nav .marca small{display:block;font-style:normal;font-weight:700;font-size:2.1vh;margin-top:.8vh}
nav .marca::after{content:"";position:absolute;right:-2vw;bottom:-2vh;width:9vw;height:9vh;background:repeating-linear-gradient(135deg,rgba(255,255,255,.9) 0 .8vh,transparent .8vh 1.8vh);transform:skewX(-20deg);opacity:.9}

/* palco */
main{flex:1;position:relative;min-width:0;padding:2vh 1.6vw 2.4vh 0}
.slide{position:absolute;inset:2vh 1.6vw 2.4vh 0;display:none;flex-direction:column}
.slide.ativo{display:flex;animation:entra .8s ease}
@keyframes entra{from{opacity:0}to{opacity:1}}
h1{font-size:4.2vh;font-weight:900;line-height:1.1}
.sub{font-size:2vh;color:#555;margin:.6vh 0 2vh}

.cd{background:var(--branco);border-radius:1.6vh;overflow:hidden;display:flex;flex-direction:column;box-shadow:0 .3vh 1.4vh rgba(0,0,0,.08);min-height:0}
.cd-h{background:var(--verm);color:var(--branco);font-weight:900;font-size:2.2vh;padding:1.2vh 1vw;display:flex;align-items:center;gap:.7vw;flex:none}
.cd-b{flex:1;padding:1.4vh 1vw;min-height:0;overflow:hidden}
.foto{background:#d9d9d9 center/cover no-repeat}
.tag{position:absolute;left:0;bottom:0;background:var(--verm);color:var(--branco);font-weight:900;font-size:1.9vh;padding:.8vh 1.2vw;border-radius:0 1.2vh 0 0}

/* calendário */
.cal{display:grid;grid-template-columns:repeat(7,1fr);gap:.3vh;text-align:center}
.cw{font-size:1.5vh;font-weight:700;color:#777;padding:.3vh 0}
.cell{display:flex;align-items:center;justify-content:center;font-size:1.8vh;aspect-ratio:1.25}
.cell span{display:flex;align-items:center;justify-content:center;width:3.1vh;height:3.1vh;border-radius:50%}
.cell.out{color:#bbb}
.cell.ev span{background:var(--rosa);color:var(--verm);font-weight:900}
.cell.hoje span{background:var(--verm);color:var(--branco);font-weight:900}

/* início */
.home{flex:1;display:grid;grid-template-columns:1.8fr 1fr;gap:2vh 1.4vw;min-height:0}
.col{display:flex;flex-direction:column;gap:2vh;min-height:0}
.hero{flex:1.25;border-radius:1.8vh;position:relative;overflow:hidden;background:linear-gradient(120deg,#171717,#3a0a0d 60%,var(--verm));display:flex;align-items:center}
.hero::before{content:"";position:absolute;inset:0;background:inherit}
.hero.foto::before{background:linear-gradient(90deg,rgba(0,0,0,.85),rgba(0,0,0,.2))}
.hero div{position:relative;color:var(--branco);padding:0 2.4vw}
.hero h2{font-size:5.6vh;line-height:1.05}
.hero p{font-size:2.2vh;margin-top:1.4vh;max-width:30vw}
.mini{display:flex;gap:1.2vw;align-items:flex-start}
.mini .cal{flex:1.1}.mini .cell{aspect-ratio:1.5;font-size:1.6vh}.mini .cell span{width:2.7vh;height:2.7vh}
.evl{flex:1;display:flex;flex-direction:column;gap:1.3vh;padding-top:.6vh}
.evl p{font-size:1.9vh;line-height:1.25;padding-left:1.1vw;position:relative}
.evl p::before{content:"";position:absolute;left:0;top:.7vh;width:.7vh;height:.7vh;border-radius:50%;background:var(--verm)}
.evl p b{color:var(--verm)}
.tiles{flex:.55;display:flex;gap:1.2vw;min-height:0}
.tile{flex:1;border-radius:1.6vh;padding:0 1.6vw;display:flex;align-items:center;gap:1.2vw;color:var(--branco);background:var(--preto);font-size:2.4vh;line-height:1.2;overflow:hidden}
.tile b{font-size:3vh;display:block;margin-bottom:.4vh}
.tile.r{background:var(--verm)}
.tile .ico{width:6vh;height:6vh;stroke-width:1.8}
.card-menu{display:flex;flex-direction:column;gap:1.1vh}
.card-menu p{font-size:1.9vh;line-height:1.3}.card-menu p b{color:var(--verm);display:inline-block;min-width:3.4vw}
.avs{display:grid;grid-template-columns:repeat(4,1fr);gap:.6vw;text-align:center;align-items:start}
.av{position:relative;width:8.6vh;height:8.6vh;margin:0 auto .6vh}
.av .foto{width:100%;height:100%;border-radius:50%;border:.4vh solid var(--verm)}
.av i{position:absolute;left:50%;bottom:-.8vh;transform:translateX(-50%);background:var(--verm);color:#fff;font:700 1.4vh Arial;font-style:normal;padding:.3vh .6vw;border-radius:2vh}
.avs p{font-size:1.6vh;line-height:1.15;margin-top:1vh}

/* notícias / comunicados */
.row{flex:1;display:flex;gap:1.4vw;min-height:0}
.nc{flex:1;display:flex;flex-direction:column}
.nc .ph{flex:1.3;position:relative;min-height:0}.nc .ph .foto{position:absolute;inset:0}
.nc .tx{flex:1;padding:1.8vh 1.2vw;display:flex;flex-direction:column}
.nc h2{font-size:2.8vh;line-height:1.2}.nc p{font-size:2vh;color:#444;margin-top:1vh;line-height:1.35}
.nc time{margin-top:auto;font-size:1.8vh;color:#666}
.promo{flex:.9;background:var(--verm);color:var(--branco);border-radius:1.8vh;display:flex;flex-direction:column;overflow:hidden}
.promo .t{padding:3vh 1.4vw;text-align:center;font-size:2vh}
.promo .t b{display:block;font-size:3.6vh;line-height:1.1;margin:1vh 0}
.promo .t svg{width:9vh;height:9vh;stroke-width:1.5}
.promo .foto{flex:1;margin:0 1vw 1vw;border-radius:1.4vh;background-color:var(--vermesc)}
.vazio{align-items:center;justify-content:center;color:#888;font-size:2.4vh;text-align:center;flex:1}

/* calendário grande */
.big .cell{aspect-ratio:auto;height:8vh;font-size:2.6vh}.big .cell span{width:5.4vh;height:5.4vh}.big .cw{font-size:2vh}
.nav-mes{display:flex;justify-content:center;gap:3vw;font-weight:900;font-size:2.6vh;margin-bottom:1.2vh}
.pe{display:flex;gap:1vw;align-items:center;padding:1.2vh 0;border-bottom:1px solid #eee}
.pe .db{background:var(--verm);color:#fff;border-radius:1.2vh;min-width:6vw;text-align:center;padding:.8vh 0;font-weight:900;font-size:3.6vh;line-height:1}
.pe .db span{display:block;font-size:1.7vh;margin-top:.4vh}
.pe b{font-size:2.5vh}.pe small{display:block;font-size:1.8vh;color:#555;margin-top:.4vh}
.ilus{margin-top:auto;align-self:flex-end;width:11vh;height:11vh;color:var(--verm);stroke-width:1.2}

/* cardápio */
.cardp{display:flex;flex-direction:column;gap:1.6vh;font-size:3vh}
.cardp p{line-height:1.3}.cardp b{display:inline-block;color:var(--verm);min-width:6vw}
/* aniversariantes grande */
.bday{flex:1;display:flex;justify-content:space-around;align-items:center}
.bday .av{width:21vh;height:21vh}.bday .av i{font-size:2.2vh;padding:.5vh 1.2vw}
.bday h2{font-size:3vh;text-align:center;margin-top:2vh}

/* carrossel */
.car{overflow:hidden;width:100%}
.trk{display:flex;transition:transform 1.4s cubic-bezier(.65,0,.35,1)}
.pg{flex:0 0 100%;min-width:0}
.cnt{margin-left:auto;font-size:1.6vh;font-weight:700}
.fest .party{position:relative;flex:1;overflow:hidden;background:linear-gradient(180deg,#fff,#fff0f1)}
.party .car{position:relative;z-index:2;height:100%}.party .trk{height:100%}
.bday{display:grid;grid-template-columns:repeat(4,1fr);gap:1.4vw;align-items:center;padding:0 2vw;height:100%}
.bi{text-align:center;transform:rotate(var(--r,0deg))}
.pg.on .bi{animation:pop 1s cubic-bezier(.2,1.4,.4,1) both}
.pg.on .bi:nth-child(2){animation-delay:.2s}.pg.on .bi:nth-child(3){animation-delay:.4s}.pg.on .bi:nth-child(4){animation-delay:.6s}
@keyframes pop{from{opacity:0;transform:translateY(8vh) scale(.4) rotate(var(--r,0deg))}to{opacity:1;transform:rotate(var(--r,0deg))}}
.bi .av{width:23vh;height:23vh;animation:flutua 3.4s ease-in-out infinite}
.bi:nth-child(even) .av{animation-delay:-1.7s}
@keyframes flutua{50%{transform:translateY(-1.6vh)}}
.bi .av .foto{border-width:.9vh;box-shadow:0 1.2vh 2.4vh rgba(227,6,19,.35)}
.bi .av i{font-size:2.4vh;padding:.7vh 1.3vw;bottom:-1.4vh}
.bi h2{font-size:3.1vh;margin-top:2.6vh;line-height:1.15}
.cf{position:absolute;top:-3vh;width:1vh;height:1.9vh;z-index:1;animation:cai linear infinite}
@keyframes cai{to{transform:translateY(80vh) rotate(720deg)}}
.ball{position:absolute;bottom:-14vh;z-index:1;width:6vh;height:7.4vh;border-radius:50% 50% 48% 48%;opacity:.8;animation:sobe linear infinite}
.ball::after{content:"";position:absolute;left:50%;top:100%;width:2px;height:6vh;background:#999}
@keyframes sobe{to{transform:translateY(-95vh) translateX(3vh)}}
.pill{position:absolute;right:1.4vw;bottom:1.4vh;z-index:3;background:var(--verm);color:#fff;padding:.7vh 1.3vw;border-radius:3vh}
.prog{position:fixed;left:0;bottom:0;height:.9vh;background:var(--verm);width:0;z-index:5}

/* admin / login */
.adm-bar{position:fixed;right:1.2vw;bottom:2vh;z-index:20;display:flex;gap:.8vw}
.adm-bar a,.adm-bar button{background:var(--preto);color:var(--branco);border:0;padding:1.2vh 1.4vw;border-radius:1vh;font:700 1.8vh Arial;text-decoration:none;cursor:pointer}
.login,.painel{position:fixed;inset:0;background:rgba(23,23,23,.85);display:none;align-items:center;justify-content:center;z-index:30}
.login.aberto,.painel.aberto{display:flex}
.login form{background:var(--branco);padding:4vh 3vw;border-radius:2vh;width:min(90vw,380px);border-top:1vh solid var(--verm)}
.login h3{font-size:3vh;margin-bottom:2vh}
.login input{width:100%;padding:1.4vh;font-size:2.2vh;border:2px solid #ccc;border-radius:1vh;margin-bottom:1.6vh}
.login button{background:var(--verm);color:var(--branco);border:0;padding:1.4vh 2vw;border-radius:1vh;font:700 2.2vh Arial;cursor:pointer}
.login .erro{color:var(--verm);margin-bottom:1vh;font-size:2vh}
.painel iframe{width:min(94vw,900px);height:92vh;border:0;border-radius:2vh;background:var(--cinza)}
.painel .fechar{position:absolute;top:2vh;right:2vw;background:var(--verm);color:var(--branco);border:0;border-radius:1vh;padding:1vh 1.4vw;font:700 2vh Arial;cursor:pointer}
</style>
</head>
<body class="<?= $adm ? 'adm-ativo' : ($loginAberto ? 'login-aberto' : '') ?>">

<header>
  <div class="logo">SESI</div>
  <div class="sep"></div>
  <div class="nome"><b>Sesi</b><span>Conectando pessoas, compartilhando informações.</span></div>
  <div class="rel"><b id="h">--:--</b><span id="dt"></span></div>
</header>

<div class="corpo">
<nav>
  <?php foreach ($menu as $k => $m): ?>
  <div class="it" data-m="<?= $k ?>"><svg class="i" viewBox="0 0 24 24"><?= $ic[$m[0]] ?></svg><?= $m[1] ?></div>
  <?php endforeach; ?>
  <div class="marca">SESI<small>Educação que transforma.</small></div>
</nav>

<main>

<!-- INÍCIO -->
<section class="slide" data-m="0">
  <div class="home">
    <div class="col">
      <div class="hero <?= $heroImg ? 'foto' : '' ?>" <?= bg($heroImg) ?>>
        <div><h2>Bem-vindo(a) ao Sesi</h2><p>Aqui você encontra tudo o que precisa para se manter informado.</p></div>
      </div>
      <div class="cd" style="flex:1.1">
        <div class="cd-h"><svg class="i" viewBox="0 0 24 24"><?= $ic['cal'] ?></svg>Calendário de Eventos</div>
        <div class="cd-b mini">
          <?= calendario($ano, $mes, $marcados, $hojeDia) ?>
          <div class="evl">
            <?php if ($proximos): foreach ($proximos as $ev): ?>
            <p><b><?= dm($ev['data']) ?></b> – <?= e($ev['titulo']) ?></p>
            <?php endforeach; else: ?><p>Nenhum evento cadastrado.</p><?php endif; ?>
          </div>
        </div>
      </div>
      <div class="tiles">
        <div class="tile"><svg class="i ico" viewBox="0 0 24 24"><?= $ic['tv'] ?><path d="M10 9l5 3-5 3z"/></svg><div><b>Assista à TV Escolar!</b>Conteúdos, entrevistas e muito mais.</div></div>
        <div class="tile r"><svg class="i ico" viewBox="0 0 24 24"><?= $ic['mail'] ?></svg><div><b>Fique por dentro!</b><?= $ultCom ? e($ultCom['titulo']) : 'Acesse os comunicados e não perca nenhuma novidade.' ?></div></div>
      </div>
    </div>
    <div class="col">
      <div class="cd" style="flex:1.15">
        <div class="cd-h"><svg class="i" viewBox="0 0 24 24"><?= $ic['food'] ?></svg>Cardápio da Semana</div>
        <div class="cd-b card-menu">
          <?php $ln = linhasCardapio($cardapio['texto'] ?? ''); if ($ln): foreach (array_slice($ln, 0, 6) as $l): ?>
          <p><?php if ($l[0]): ?><b><?= e($l[0]) ?></b><?php endif; ?><?= e($l[1]) ?></p>
          <?php endforeach; else: ?><p>Cardápio ainda não publicado.</p><?php endif; ?>
        </div>
      </div>
      <div class="cd" style="flex:1">
        <div class="cd-h"><svg class="i" viewBox="0 0 24 24"><?= $ic['cake'] ?></svg>Aniversariantes do Mês<span class="cnt"></span></div>
        <div class="cd-b">
          <?php if ($aniversarios): ?>
          <div class="car" data-key="car_home" data-ms="<?= MS_CARROSSEL_HOME ?>"><div class="trk">
            <?php foreach ($paginasAniv as $pg): ?>
            <div class="pg avs">
              <?php foreach ($pg as $a): ?>
              <div><div class="av"><div class="foto" <?= bg($a['img']) ?>></div><i><?= dm($a['data']) ?></i></div><p><?= e($a['titulo']) ?></p></div>
              <?php endforeach; ?>
            </div>
            <?php endforeach; ?>
          </div></div>
          <?php else: ?><p style="font-size:1.9vh">Nenhum aniversariante cadastrado.</p><?php endif; ?>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- NOTÍCIAS -->
<?php if ($noticias): ?>
<section class="slide" data-m="2">
  <h1>Notícias</h1><div class="sub">Fique por dentro de tudo o que acontece no Sesi!</div>
  <div class="row">
    <?php foreach (array_reverse($noticias) as $n): ?>
    <article class="cd nc">
      <div class="ph"><div class="foto" <?= bg($n['img']) ?>></div><span class="tag">Notícia</span></div>
      <div class="tx"><h2><?= e($n['titulo']) ?></h2><?php if (!empty($n['texto'])): ?><p><?= e(mb_strimwidth($n['texto'], 0, 140, '…')) ?></p><?php endif; ?><time><?= date('d/m/Y', strtotime($n['criado'])) ?></time></div>
    </article>
    <?php endforeach; ?>
    <div class="promo">
      <div class="t"><svg class="i" viewBox="0 0 24 24"><path d="M8 4h8v6a4 4 0 01-8 0zM8 6H4v1a4 4 0 004 4M16 6h4v1a4 4 0 01-4 4M12 14v4M8 21h8"/></svg><b>Sesi incentivando</b>talentos, construindo futuros.</div>
      <div class="foto" <?= bg($ultCom['img'] ?? '') ?>></div>
    </div>
  </div>
</section>
<?php endif; ?>

<!-- COMUNICADOS -->
<?php if ($comunicados): ?>
<section class="slide" data-m="1">
  <h1>Comunicados</h1><div class="sub">Fique por dentro dos avisos da nossa escola.</div>
  <div class="row">
    <?php foreach (array_reverse($comunicados) as $c): ?>
    <article class="cd nc">
      <?php if (!empty($c['img'])): ?><div class="ph"><div class="foto" <?= bg($c['img']) ?>></div><span class="tag">Comunicado</span></div><?php endif; ?>
      <div class="tx"><?php if (empty($c['img'])): ?><span class="tag" style="position:static;display:inline-block;border-radius:1vh;align-self:flex-start;margin-bottom:1.4vh">Comunicado</span><?php endif; ?>
        <h2><?= e($c['titulo']) ?></h2><?php if (!empty($c['texto'])): ?><p><?= e(mb_strimwidth($c['texto'], 0, 260, '…')) ?></p><?php endif; ?></div>
    </article>
    <?php endforeach; ?>
  </div>
</section>
<?php endif; ?>

<!-- CALENDÁRIO -->
<section class="slide" data-m="4">
  <h1>Calendário de Eventos</h1><div class="sub">Confira os próximos eventos e atividades da nossa escola!</div>
  <div class="row">
    <div class="cd big" style="flex:1.5"><div class="cd-b">
      <div class="nav-mes"><span>‹</span><span><?= $MES[$mes] ?> de <?= $ano ?></span><span>›</span></div>
      <?= calendario($ano, $mes, $marcados, $hojeDia) ?>
    </div></div>
    <div class="cd" style="flex:1"><div class="cd-h">Próximos Eventos</div>
      <div class="cd-b" style="display:flex;flex-direction:column">
        <?php if ($proximos): foreach ($proximos as $ev): $t = strtotime($ev['data'] ?? ''); ?>
        <div class="pe"><div class="db"><?= $t ? date('d', $t) : '–' ?><span><?= $t ? $MESC[(int)date('n', $t)] : '' ?></span></div>
          <div><b><?= e($ev['titulo']) ?></b><?php if (!empty($ev['texto'])): ?><small><?= e(mb_strimwidth($ev['texto'], 0, 70, '…')) ?></small><?php endif; ?></div></div>
        <?php endforeach; else: ?><div class="vazio">Nenhum evento cadastrado.</div><?php endif; ?>
        <svg class="i ilus" viewBox="0 0 24 24"><?= $ic['cal'] ?></svg>
      </div></div>
  </div>
</section>

<!-- CARDÁPIO -->
<?php if ($cardapio): ?>
<section class="slide" data-m="5">
  <h1><?= e($cardapio['titulo']) ?></h1><div class="sub">O que vamos comer esta semana.</div>
  <div class="row">
    <?php if (!empty($cardapio['img'])): ?><div class="cd foto" <?= bg($cardapio['img'], 'flex:1') ?>></div><?php endif; ?>
    <div class="cd" style="flex:1.2"><div class="cd-h"><svg class="i" viewBox="0 0 24 24"><?= $ic['food'] ?></svg>Cardápio da Semana</div>
      <div class="cd-b cardp"><?php foreach (linhasCardapio($cardapio['texto'] ?? '') as $l): ?><p><?php if ($l[0]): ?><b><?= e($l[0]) ?></b><?php endif; ?><?= e($l[1]) ?></p><?php endforeach; ?></div></div>
  </div>
</section>
<?php endif; ?>

<!-- ANIVERSARIANTES -->
<?php if ($aniversarios): ?>
<section class="slide fest" data-m="6" data-dur="<?= $durFesta ?>">
  <h1>Aniversariantes de <?= $MES[$mes] ?> 🎉</h1><div class="sub">Parabéns a todos! Que o dia de vocês seja cheio de alegria.</div>
  <div class="cd party">
    <?php for ($k = 0; $k < 30; $k++): ?><i class="cf" style="left:<?= mt_rand(1, 99) ?>%;background:<?= $cores[$k % 5] ?>;animation-duration:<?= mt_rand(6, 11) ?>s;animation-delay:-<?= mt_rand(0, 11) ?>s"></i><?php endfor; ?>
    <?php for ($k = 0; $k < 7; $k++): ?><i class="ball" style="left:<?= 4 + $k * 14 ?>%;background:<?= $cores[$k % 5] ?>;animation-duration:<?= mt_rand(14, 22) ?>s;animation-delay:-<?= mt_rand(0, 20) ?>s"></i><?php endfor; ?>
    <span class="cnt pill"></span>
    <div class="car" data-key="car_festa" data-ms="<?= MS_CARROSSEL_FESTA ?>"><div class="trk">
      <?php foreach ($paginasAniv as $pg): ?>
      <div class="pg bday">
        <?php foreach ($pg as $n => $a): ?>
        <div class="bi" style="--r:<?= [-3, 2, -2, 3][$n % 4] ?>deg">
          <div class="av"><div class="foto" <?= bg($a['img']) ?>></div><i>🎂 <?= dm($a['data']) ?></i></div>
          <h2><?= e($a['titulo']) ?></h2>
        </div>
        <?php endforeach; ?>
      </div>
      <?php endforeach; ?>
    </div></div>
  </div>
</section>
<?php endif; ?>

<div class="prog" id="prog"></div>
</main>
</div>

<?php if ($adm): ?>
<div class="adm-bar">
  <button type="button" onclick="document.getElementById('painel').classList.add('aberto')">Abrir formulário</button>
  <a href="?sair=1">Sair</a>
</div>
<div class="painel" id="painel">
  <button class="fechar" type="button" onclick="document.getElementById('painel').classList.remove('aberto');location.reload()">Fechar</button>
  <iframe src="formulario.php" title="Formulário"></iframe>
</div>
<?php else: ?>
<div class="login <?= $loginAberto ? 'aberto' : '' ?>" id="login">
  <form method="post">
    <h3>Acesso do administrador</h3>
    <?php if ($erro): ?><div class="erro"><?= e($erro) ?></div><?php endif; ?>
    <input type="password" name="senha" placeholder="Senha" autocomplete="current-password" required>
    <button type="submit">Entrar</button>
  </form>
</div>
<?php endif; ?>

<script>
const TEMPO = <?= TEMPO_SLIDE ?> * 1000;
const slides = [...document.querySelectorAll('.slide')];
const itens  = [...document.querySelectorAll('nav .it')];
const prog = document.getElementById('prog');
let i = 0, pausado = <?= $loginAberto ? 'true' : 'false' ?>;

let tmr;
function mostrar(n){
  const dur = +slides[n].dataset.dur || TEMPO;
  slides.forEach(s => s.classList.remove('ativo'));
  slides[n].classList.add('ativo');
  itens.forEach(x => x.classList.toggle('on', x.dataset.m === slides[n].dataset.m));
  prog.style.transition = 'none'; prog.style.width = '0';
  requestAnimationFrame(() => requestAnimationFrame(() => {
    prog.style.transition = 'width ' + dur + 'ms linear'; prog.style.width = '100%';
  }));
  iniciaCar(slides[n]);
  clearTimeout(tmr); tmr = setTimeout(proximo, dur);
}
function proximo(){
  if (pausado){ tmr = setTimeout(proximo, 1000); return; }
  i++;
  if (i >= slides.length){ location.reload(); return; }
  mostrar(i);
}

/* carrosséis: retomam de onde pararam no ciclo anterior */
const cars = [...document.querySelectorAll('.car')].map(el => {
  const pgs = [...el.querySelector('.trk').children];
  let idx = 0;
  try { const g = localStorage.getItem(el.dataset.key); if (g !== null) idx = (parseInt(g) + 1) % pgs.length; } catch(e){}
  const c = {el, trk: el.querySelector('.trk'), pgs, idx, ms: +el.dataset.ms, timer: null, slide: el.closest('.slide')};
  irPara(c, false); return c;
});
function irPara(c, anim){
  c.trk.style.transition = anim ? '' : 'none';
  c.trk.style.transform = 'translateX(-' + (c.idx * 100) + '%)';
  c.pgs.forEach((p, k) => p.classList.toggle('on', k === c.idx));
  const n = c.slide.querySelector('.cnt'); if (n) n.textContent = (c.idx + 1) + ' / ' + c.pgs.length;
  try { localStorage.setItem(c.el.dataset.key, c.idx); } catch(e){}
}
function iniciaCar(sl){
  cars.forEach(c => {
    clearInterval(c.timer);
    if (c.slide === sl && c.pgs.length > 1) c.timer = setInterval(() => { c.idx = (c.idx + 1) % c.pgs.length; irPara(c, true); }, c.ms);
  });
}
mostrar(0);

function relogio(){
  const d = new Date();
  document.getElementById('h').textContent = d.toLocaleTimeString('pt-BR',{hour:'2-digit',minute:'2-digit'});
  document.getElementById('dt').textContent = d.toLocaleDateString('pt-BR',{weekday:'long',day:'numeric',month:'long'});
}
relogio(); setInterval(relogio, 15000);

const login = document.getElementById('login');
if (login){
  document.addEventListener('keydown', e => {
    if (e.key.toLowerCase() === 'l' && !login.classList.contains('aberto')){
      login.classList.add('aberto'); document.body.classList.add('login-aberto'); pausado = true;
      login.querySelector('input').focus(); e.preventDefault();
    } else if (e.key === 'Escape'){
      login.classList.remove('aberto'); document.body.classList.remove('login-aberto'); pausado = false;
    }
  });
}
<?php if ($adm): ?>
setInterval(() => { pausado = document.getElementById('painel').classList.contains('aberto'); }, 500);
<?php endif; ?>
</script>
</body>
</html>
