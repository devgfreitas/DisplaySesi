<div class="card h">
  <div class="entre"><h2 class="m0">Publicações</h2><a class="btn" href="<?= url('painel/publicacoes/nova') ?>">+ Nova publicação</a></div>
  <form method="get" action="<?= url('painel/publicacoes') ?>" class="filtros mt14">
    <div><label>Busca</label><input name="q" value="<?= h($f['q']) ?>" placeholder="título ou texto"></div>
    <div><label>Categoria</label><select name="tipo"><option value="">Todas</option><?= opts(config('app.tipos'), $f['tipo']) ?></select></div>
    <div><label>Status</label><select name="status"><option value="">Todos</option><?= opts(config('app.status'), $f['status']) ?></select></div>
    <div><label>Turma</label><select name="turma"><option value="">Todas</option><?= opts($turmas, $f['turma']) ?></select></div>
    <div><label>De</label><input type="date" name="de" value="<?= h($f['de']) ?>"></div>
    <div><label>Até</label><input type="date" name="ate" value="<?= h($f['ate']) ?>"></div>
    <button class="s">Filtrar</button>
  </form>
</div>

<div class="card ov-x">
  <table>
    <tr><th>Título</th><th>Tipo</th><th>Público</th><th>Exibição</th><th>Status</th><th>Ações</th></tr>
    <?php foreach ($rows as $r): ?>
    <tr>
      <td><b><?= h($r['titulo']) ?></b><br><small><?= h($r['autor']) ?> · <?= h(config('app.visibilidade.' . $r['visibilidade'], '')) ?></small>
        <?php if ($r['status'] === 'rejeitado' && $r['motivo']): ?><div class="hist">Motivo: <?= h($r['motivo']) ?></div><?php endif; ?></td>
      <td><?= h(config('app.tipos.' . $r['tipo'], $r['tipo'])) ?><?= $r['disciplina'] ? '<br><small>' . h($r['disciplina']) . '</small>' : '' ?></td>
      <td><?= $r['publico_alvo'] === 'geral' ? 'Geral' : h($r['turmas']) ?><?= $r['exibir_tv'] ? '<br><span class="tvx">▶ TV</span>' : '' ?></td>
      <td><small><?= $r['inicio_exibicao'] ? 'de ' . h(dh_br($r['inicio_exibicao'])) : 'imediato' ?><br><?= $r['fim_exibicao'] ? 'até ' . h(dh_br($r['fim_exibicao'])) : 'sem validade' ?></small></td>
      <td><?= badge($r) ?></td>
      <td>
        <a class="btn p c" href="<?= url('painel/publicacoes/' . (int)$r['id'] . '/editar') ?>">Editar</a>
        <?php if (in_array($r['status'], ['rascunho', 'rejeitado'], true)) echo form_acao('painel/publicacoes/' . (int)$r['id'] . '/enviar', 'Enviar p/ revisão', 's'); ?>
        <?php if ($r['status'] !== 'arquivado') echo form_acao('painel/publicacoes/' . (int)$r['id'] . '/arquivar', 'Arquivar', 'c', 'Arquivar esta publicação?'); ?>
      </td>
    </tr>
    <?php endforeach; if (!$rows): ?><tr><td colspan="6">Nenhuma publicação encontrada.</td></tr><?php endif; ?>
  </table>
</div>
