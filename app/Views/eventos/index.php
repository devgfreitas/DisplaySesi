<div class="card h"><h2><?= $id ? 'Editar evento' : 'Novo evento' ?></h2>
  <form method="post" action="<?= url('painel/eventos') ?>" enctype="multipart/form-data">
    <?= csrf_campo() ?><input type="hidden" name="id" value="<?= (int)$id ?>">
    <div class="g2">
      <div><label>Título</label><input name="titulo" required value="<?= h($e['titulo']) ?>"></div>
      <div><label>Tipo</label><select name="tipo"><?= opts(config('app.tipos_evento'), $e['tipo']) ?></select></div>
    </div>
    <div class="g3">
      <div><label>Data de início</label><input type="date" name="data_inicio" required value="<?= h($e['data_inicio']) ?>"></div>
      <div><label>Data final (opcional)</label><input type="date" name="data_fim" value="<?= h($e['data_fim']) ?>"></div>
      <div><label>Local</label><input name="local" value="<?= h($e['local']) ?>"></div>
    </div>
    <label>Descrição</label><textarea name="descricao" class="min70"><?= h($e['descricao']) ?></textarea>
    <label>Imagem (opcional)</label><input type="file" name="imagem" accept="image/jpeg,image/png,image/webp,image/gif">
    <label class="chk"><input type="checkbox" name="publico" value="1" <?= $e['publico'] ? 'checked' : '' ?>> Mostrar no calendário da TV (desmarque para reuniões internas)</label>
    <div class="acoes"><button>Salvar evento</button><?php if ($id): ?><a class="btn c" href="<?= url('painel/eventos') ?>">Cancelar</a><?php endif; ?></div>
  </form>
</div>

<div class="card"><h3>Calendário escolar</h3>
  <table>
    <tr><th>Data</th><th>Evento</th><th>Tipo</th><th>TV</th><th></th></tr>
    <?php foreach ($eventos as $r): ?>
    <tr>
      <td><b><?= h(d_br($r['data_inicio'])) ?></b><?= $r['data_fim'] ? '<br><small>até ' . h(d_br($r['data_fim'])) . '</small>' : '' ?></td>
      <td><?= h($r['titulo']) ?><?= $r['local'] ? '<br><small>' . h($r['local']) . '</small>' : '' ?></td>
      <td><?= h(config('app.tipos_evento.' . $r['tipo'], '')) ?></td>
      <td><?= $r['publico'] ? '<span class="tvx">▶ público</span>' : '—' ?></td>
      <td><a class="btn p c" href="<?= url('painel/eventos/' . (int)$r['id'] . '/editar') ?>">Editar</a>
        <?php if (pode('revisor')) echo form_acao('painel/eventos/' . (int)$r['id'] . '/excluir', 'Excluir', 's', 'Excluir evento?'); ?></td>
    </tr>
    <?php endforeach; ?>
  </table>
</div>
