<?php
/* Layout das páginas abertas ao público (consulta e cardápio). */
$flash = flash_pegar();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Sesi · <?= h($titulo ?? '') ?></title>
<link rel="stylesheet" href="<?= asset('css/painel.css') ?>">
</head>
<body>
<div class="top">
  <span class="logo">SESI</span><h1><?= h($titulo ?? '') ?></h1>
  <span class="quem"><a href="<?= url('consulta') ?>">Consulta</a> · <a href="<?= url('cardapio') ?>">Cardápio</a></span>
</div>
<main class="c">
  <?php if ($flash): ?><div class="<?= $flash[0] === 'ok' ? 'ok' : 'er' ?>"><?= h($flash[1]) ?></div><?php endif; ?>
  <?= $conteudo ?>
</main>
<script src="<?= asset('js/painel.js') ?>"></script>
</body>
</html>
