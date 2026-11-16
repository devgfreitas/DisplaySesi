<?php if (!$c): ?>
  <div class="card"><p>Nenhum cardápio publicado no momento.</p></div>
<?php else: ?>
  <div class="noprint acoes mb14">
    <button data-print>🖨 Imprimir</button>
    <?php foreach ($outras as $o): if ((int)$o['id'] === (int)$c['id']) continue; ?>
      <a class="btn c" href="<?= url('cardapio/' . (int)$o['id']) ?>">Semana de <?= h(date('d/m', strtotime($o['semana_inicio']))) ?></a>
    <?php endforeach; ?>
  </div>
  <div class="card folha h">
    <h1>Sesi · <?= h($c['titulo'] ?: 'Cardápio da semana') ?></h1>
    <div class="periodo">Semana de <?= h(d_br($c['semana_inicio'])) ?> a <?= h(date('d/m/Y', strtotime($c['semana_inicio'] . ' +4 day'))) ?>
      <?= $c['status'] !== 'publicado' ? ' · <b>RASCUNHO (não publicado)</b>' : '' ?></div>
    <div class="ov-x"><table class="grade cardapio">
      <tr><th></th><?php foreach (config('app.dias') as $d => $nome): ?><th><?= h($nome) ?><br><small><?= h(date('d/m', strtotime($c['semana_inicio'] . ' +' . ($d - 1) . ' day'))) ?></small></th><?php endforeach; ?></tr>
      <?php foreach (config('app.refeicoes') as $ref => $rot): ?>
        <tr><td class="ref"><?= h($rot) ?></td>
          <?php foreach (config('app.dias') as $d => $_): ?><td><?= h($g[$d][$ref] ?? '–') ?></td><?php endforeach; ?></tr>
      <?php endforeach; ?>
    </table></div>
    <div class="rodape">Cardápio sujeito a alterações. Sesi – Educação que transforma.</div>
  </div>
<?php endif; ?>
