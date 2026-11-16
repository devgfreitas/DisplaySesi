<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Sesi</title>
<link rel="stylesheet" href="<?= asset('css/tv.css') ?>">
</head>
<body class="<?= $adm ? 'adm-ativo' : ($loginAberto ? 'login-aberto' : '') ?>"
      data-tempo="<?= (int)$tempoSlide * 1000 ?>" data-versao="<?= h($versao) ?>" data-polling="<?= (int)$pollingSeg ?>"
      data-url-versao="<?= h(url('tv/versao')) ?>" data-login="<?= $loginAberto ? '1' : '0' ?>">

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
      <div class="cd f11">
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
      <div class="cd f115">
        <div class="cd-h"><svg class="i" viewBox="0 0 24 24"><?= $ic['food'] ?></svg>Cardápio da Semana<?= !empty($cardapio['periodo']) ? ' · ' . e($cardapio['periodo']) : '' ?></div>
        <div class="cd-b card-menu">
          <?php $ln = linhasCardapio($cardapio['texto'] ?? ''); if ($ln): foreach (array_slice($ln, 0, 6) as $l): ?>
          <p><?php if ($l[0]): ?><b><?= e($l[0]) ?></b><?php endif; ?><?= e($l[1]) ?></p>
          <?php endforeach; else: ?><p>Cardápio ainda não publicado.</p><?php endif; ?>
        </div>
      </div>
      <div class="cd f1">
        <div class="cd-h"><svg class="i" viewBox="0 0 24 24"><?= $ic['cake'] ?></svg>Aniversariantes do Mês<span class="cnt"></span></div>
        <div class="cd-b">
          <?php if ($aniversarios): ?>
          <div class="car" data-key="car_home" data-ms="<?= $msHome ?>"><div class="trk">
            <?php foreach ($paginasAniv as $pg): ?>
            <div class="pg avs">
              <?php foreach ($pg as $a): ?>
              <div><div class="av"><div class="foto" <?= bg($a['img']) ?>></div><i><?= dm($a['data']) ?></i></div><p><?= e($a['titulo']) ?></p></div>
              <?php endforeach; ?>
            </div>
            <?php endforeach; ?>
          </div></div>
          <?php else: ?><p class="txt-sm">Nenhum aniversariante cadastrado.</p><?php endif; ?>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- NOTÍCIAS -->
<?php foreach (array_chunk(array_reverse($noticias), 3) as $grupo): ?>
<section class="slide" data-m="2" data-dur="<?= durGrupo($grupo) ?>">
  <h1>Notícias</h1><div class="sub">Fique por dentro de tudo o que acontece no Sesi!</div>
  <div class="row">
    <?php foreach ($grupo as $n): ?>
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
<?php endforeach; ?>

<!-- COMUNICADOS -->
<?php foreach (array_chunk(array_reverse($comunicados), 3) as $grupo): ?>
<section class="slide" data-m="1" data-dur="<?= durGrupo($grupo) ?>">
  <h1>Comunicados</h1><div class="sub">Fique por dentro dos avisos da nossa escola.</div>
  <div class="row">
    <?php foreach ($grupo as $c): ?>
    <article class="cd nc">
      <?php if (!empty($c['img'])): ?><div class="ph"><div class="foto" <?= bg($c['img']) ?>></div><span class="tag">Comunicado</span></div><?php endif; ?>
      <div class="tx"><?php if (empty($c['img'])): ?><span class="tag tag-linha">Comunicado</span><?php endif; ?>
        <h2><?= e($c['titulo']) ?></h2><?php if (!empty($c['texto'])): ?><p><?= e(mb_strimwidth($c['texto'], 0, 260, '…')) ?></p><?php endif; ?></div>
    </article>
    <?php endforeach; ?>
  </div>
</section>
<?php endforeach; ?>

<!-- CALENDÁRIO -->
<section class="slide" data-m="4">
  <h1>Calendário de Eventos</h1><div class="sub">Confira os próximos eventos e atividades da nossa escola!</div>
  <div class="row">
    <div class="cd big f15"><div class="cd-b">
      <div class="nav-mes"><span>‹</span><span><?= $MES[$mes] ?> de <?= $ano ?></span><span>›</span></div>
      <?= calendario($ano, $mes, $marcados, $hojeDia) ?>
    </div></div>
    <div class="cd f1"><div class="cd-h">Próximos Eventos</div>
      <div class="cd-b col-flex">
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
    <?php if (!empty($cardapio['img'])): ?><div class="cd foto f1" <?= bg($cardapio['img']) ?>></div><?php endif; ?>
    <div class="cd f12"><div class="cd-h"><svg class="i" viewBox="0 0 24 24"><?= $ic['food'] ?></svg>Cardápio da Semana</div>
      <div class="cd-b cardp"><?php foreach (linhasCardapio($cardapio['completo'] ?? $cardapio['texto'] ?? '') as $l): ?><p><?php if ($l[0]): ?><b><?= e($l[0]) ?></b><?php endif; ?><?= e($l[1]) ?></p><?php endforeach; ?></div></div>
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
    <div class="car" data-key="car_festa" data-ms="<?= $msFesta ?>"><div class="trk">
      <?php foreach ($paginasAniv as $pg): ?>
      <div class="pg bday">
        <?php foreach ($pg as $n => $a): ?>
        <div class="bi rot<?= $n % 4 ?>">
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
  <a href="<?= url('painel') ?>" target="_blank">Abrir painel</a>
  <a href="<?= url('sair') ?>">Sair</a>
</div>
<?php else: ?>
<div class="login <?= $loginAberto ? 'aberto' : '' ?>" id="login">
  <form method="post" action="<?= url('login') ?>">
    <?= csrf_campo() ?>
    <h3>Central de conteúdo – acesso da equipe</h3>
    <?php if ($erro && $erro[0] === 'err'): ?><div class="erro"><?= h($erro[1]) ?></div><?php endif; ?>
    <input type="email" name="email" placeholder="E-mail" autocomplete="username" required>
    <input type="password" name="senha" placeholder="Senha" autocomplete="current-password" required>
    <button type="submit">Entrar</button>
  </form>
</div>
<?php endif; ?>

<script src="<?= asset('js/tv.js') ?>"></script>
</body>
</html>
