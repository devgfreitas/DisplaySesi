<div class="card h">
  <div class="entre">
    <h2>Semana de <?= h(d_br($c['semana_inicio'])) ?> a <?= h(date('d/m/Y', strtotime($c['semana_inicio'] . ' +4 day'))) ?>
      <span class="b b-<?= h($c['status']) ?>"><?= $c['status'] === 'publicado' ? 'Publicado' : 'Rascunho' ?></span></h2>
    <a class="btn s" target="_blank" href="<?= url('cardapio/' . (int)$c['id']) ?>">Visualizar / Imprimir</a>
  </div>
  <form method="post" action="<?= url('painel/cardapio/' . (int)$c['id']) ?>" enctype="multipart/form-data">
    <?= csrf_campo() ?>
    <label>Título</label><input name="titulo" value="<?= h($c['titulo']) ?>">
    <div class="ov-x mt14"><table class="grade">
      <tr><th>Período</th><?php foreach (config('app.dias') as $d => $nome): ?><th><?= h($nome) ?><br><small><?= h(date('d/m', strtotime($c['semana_inicio'] . ' +' . ($d - 1) . ' day'))) ?></small></th><?php endforeach; ?></tr>
      <?php foreach (config('app.refeicoes') as $ref => $rot): ?>
        <tr><th class="esq"><?= h($rot) ?></th>
          <?php foreach (config('app.dias') as $d => $_): ?><td><input name="c[<?= (int)$d ?>][<?= h($ref) ?>]" value="<?= h($g[$d][$ref] ?? '') ?>" maxlength="160"></td><?php endforeach; ?></tr>
      <?php endforeach; ?>
    </table></div>
    <label>Imagem (opcional)</label><input type="file" name="imagem" accept="image/jpeg,image/png,image/webp,image/gif">
    <div class="dica">Na tela inicial da TV aparece o período do momento (manhã, tarde ou noite); a tela do cardápio e a impressão mostram os três períodos.</div>
    <div class="acoes">
      <button name="acao" value="salvar" class="s">Salvar</button>
      <?php if (pode('revisor')): ?><button name="acao" value="publicar">Salvar e publicar</button><?php endif; ?>
      <a class="btn c" href="<?= url('painel/cardapio') ?>">Voltar</a>
    </div>
  </form>
  <?php if (pode('revisor')): ?>
  <div class="acoes">
    <?php if ($c['status'] === 'publicado') echo form_acao('painel/cardapio/' . (int)$c['id'] . '/despublicar', 'Despublicar', 'c'); ?>
    <?= form_acao('painel/cardapio/' . (int)$c['id'] . '/excluir', 'Excluir', 'c', 'Excluir este cardápio?') ?>
  </div>
  <?php endif; ?>
</div>
