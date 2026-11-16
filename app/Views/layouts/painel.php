<?php
/* Layout da área da equipe (painel). Recebe $conteudo, $titulo e $aba. */
$flash = flash_pegar();
$u = usuario();
$abas = [
    'resumo'          => ['Resumo', 'painel', null],
    'publicacoes'     => ['Publicações', 'painel/publicacoes', null],
    'revisao'         => ['Revisão', 'painel/revisao', ['revisor']],
    'cardapio'        => ['Cardápio', 'painel/cardapio', null],
    'eventos'         => ['Calendário', 'painel/eventos', null],
    'aniversariantes' => ['Aniversariantes', 'painel/aniversariantes', null],
    'turmas'          => ['Turmas', 'painel/turmas', []],
    'usuarios'        => ['Usuários', 'painel/usuarios', []],
];
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Sesi · <?= h($titulo ?? 'Central de conteúdo') ?></title>
<link rel="stylesheet" href="<?= asset('css/painel.css') ?>">
</head>
<body>
<div class="top">
  <span class="logo">SESI</span><h1>Central de conteúdo</h1>
  <span class="quem"><?= h($u['nome']) ?> (<?= h($u['perfil']) ?>) ·
    <a href="<?= url('') ?>" target="_blank">TV</a> · <a href="<?= url('consulta') ?>" target="_blank">Consulta</a> · <a href="<?= url('sair') ?>">Sair</a></span>
</div>
<nav class="abas">
  <?php foreach ($abas as $k => $a): if ($a[2] !== null && !pode(...$a[2])) continue; ?>
    <a href="<?= url($a[1]) ?>" class="<?= ($aba ?? '') === $k ? 'on' : '' ?>"><?= h($a[0]) ?></a>
  <?php endforeach; ?>
</nav>
<main class="c">
  <?php if ($flash): ?><div class="<?= $flash[0] === 'ok' ? 'ok' : 'er' ?>"><?= h($flash[1]) ?></div><?php endif; ?>
  <?= $conteudo ?>
</main>
<script src="<?= asset('js/painel.js') ?>"></script>
</body>
</html>
