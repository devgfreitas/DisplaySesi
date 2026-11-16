<h2>Resumo</h2>
<div class="cards">
  <div class="k"><b><?= (int)$ind['ativas'] ?></b><span>Publicações ativas</span></div>
  <div class="k"><b><?= (int)$ind['revisao'] ?></b><span>Aguardando revisão</span></div>
  <div class="k"><b><?= (int)$naTv ?></b><span>Cards no ar na TV</span></div>
  <div class="k"><b><?= (int)$ind['agendadas'] ?></b><span>Agendadas</span></div>
  <div class="k"><b><?= (int)$ind['rascunhos'] ?></b><span>Rascunhos</span></div>
  <div class="k"><b><?= (int)$ind['expiradas'] ?></b><span>Expiradas (no histórico)</span></div>
</div>

<div class="cols">
  <div class="card h"><h3>Próximas datas</h3>
    <table>
      <?php foreach ($datas as $l): ?>
        <tr><td><b><?= h(d_br($l[0])) ?></b></td><td><?= h($l[1]) ?><br><small><?= h($l[2]) ?></small></td></tr>
      <?php endforeach; if (!$datas): ?><tr><td>Nenhuma data próxima.</td></tr><?php endif; ?>
    </table>
  </div>
  <div class="card h"><h3>Aguardando revisão</h3>
    <?php if ($fila): ?>
      <table><?php foreach ($fila as $f): ?><tr><td><?= h($f['titulo']) ?><br><small><?= h(config('app.tipos.' . $f['tipo'])) ?> · <?= h($f['nome']) ?></small></td></tr><?php endforeach; ?></table>
      <?php if (pode('revisor')): ?><div class="acoes"><a class="btn p" href="<?= url('painel/revisao') ?>">Abrir fila de revisão</a></div><?php endif; ?>
    <?php else: ?><p>Nenhum conteúdo aguardando revisão.</p><?php endif; ?>
  </div>
</div>

<div class="cols">
  <div class="card h"><h3>Relatório: publicações por tipo e status</h3>
    <table>
      <tr><th>Tipo</th><th>Rascunho</th><th>Revisão</th><th>Aprovado</th><th>Arquivado</th></tr>
      <?php foreach ($tabela as $tp => $st): ?>
        <tr><td><?= h(config('app.tipos.' . $tp, $tp)) ?></td><td><?= $st['rascunho'] ?? 0 ?></td><td><?= ($st['em_revisao'] ?? 0) + ($st['rejeitado'] ?? 0) ?></td><td><?= $st['aprovado'] ?? 0 ?></td><td><?= $st['arquivado'] ?? 0 ?></td></tr>
      <?php endforeach; ?>
    </table>
  </div>
  <div class="card h"><h3>Mais consultadas (área de consulta)</h3>
    <?php if ($topo): ?>
      <table><?php foreach ($topo as $t): ?><tr><td><?= h($t['titulo']) ?></td><td><b><?= (int)$t['c'] ?></b> visualizações</td></tr><?php endforeach; ?></table>
    <?php else: ?><p>Ainda não há visualizações registradas.</p><?php endif; ?>
    <h3 class="mt16">Últimas atividades</h3>
    <?php foreach ($atividades as $r): ?>
      <div class="hist"><?= h($r['nome']) ?> · <b><?= h($r['acao']) ?></b> · <?= h($r['titulo']) ?> <small>(<?= h(dh_br($r['criado_em'])) ?>)</small></div>
    <?php endforeach; ?>
  </div>
</div>
