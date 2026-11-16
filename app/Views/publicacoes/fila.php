<h2>Fila de revisão</h2>
<?php if (!$rows): ?><div class="card"><p>Nenhuma publicação aguardando revisão.</p></div><?php endif; ?>
<?php foreach ($rows as $r): $bloq = $r['bloqueio']; ?>
<div class="card h">
  <div class="entre"><h3><?= h($r['titulo']) ?></h3><span><?= h(config('app.tipos.' . $r['tipo'])) ?> · <?= h($r['autor']) ?></span></div>
  <p><?= nl2br(h($r['descricao'])) ?></p>
  <p><small><b>Público:</b> <?= $r['publico_alvo'] === 'geral' ? 'Geral' : h($r['turmas']) ?> · <b>Visibilidade:</b> <?= h(config('app.visibilidade.' . $r['visibilidade'], '')) ?> ·
    <b>Exibição:</b> <?= $r['inicio_exibicao'] ? h(dh_br($r['inicio_exibicao'])) : 'imediata' ?> → <?= $r['fim_exibicao'] ? h(dh_br($r['fim_exibicao'])) : 'sem validade' ?></small></p>
  <?php if ($r['exibir_tv']): ?>
    <div class="<?= $bloq ? 'er' : 'aviso-tv' ?>"><b>Solicitou exibição na TV pública.</b>
      <?= $bloq ? ' Bloqueado: ' . h($bloq) : ' Verificação automática: nenhum dado sensível encontrado. Confirme visualmente antes de aprovar.' ?></div>
  <?php endif; ?>
  <?php foreach ($r['historico'] as $hs): ?>
    <div class="hist"><b><?= h($hs['nome']) ?></b> · <?= h($hs['acao']) ?> · <small><?= h(dh_br($hs['criado_em'])) ?></small><?= $hs['comentario'] ? '<br>' . h($hs['comentario']) : '' ?></div>
  <?php endforeach; ?>
  <form method="post" action="<?= url('painel/publicacoes/' . (int)$r['id'] . '/aprovar') ?>">
    <?= csrf_campo() ?><input type="hidden" name="voltar" value="revisao">
    <label>Comentário (obrigatório para devolver)</label><textarea name="comentario" class="min60"></textarea>
    <div class="acoes">
      <button <?= $bloq ? 'disabled class="desab"' : '' ?>>Aprovar</button>
      <button class="s" formaction="<?= url('painel/publicacoes/' . (int)$r['id'] . '/rejeitar') ?>">Devolver ao autor</button>
      <a class="btn c" href="<?= url('painel/publicacoes/' . (int)$r['id'] . '/editar') ?>">Editar</a>
    </div>
  </form>
</div>
<?php endforeach; ?>
