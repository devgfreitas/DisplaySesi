<div class="card h"><h2>Cardápio semanal</h2>
  <form method="post" action="<?= url('painel/cardapio/nova') ?>" class="g3 fim">
    <?= csrf_campo() ?>
    <div><label>Semana (qualquer dia dela)</label><input type="date" name="semana" required value="<?= date('Y-m-d') ?>"></div>
    <div><button>Criar / abrir semana</button></div>
  </form>
</div>
<div class="card">
  <table>
    <tr><th>Semana</th><th>Status</th><th>Ações</th></tr>
    <?php foreach ($cardapios as $c): ?>
    <tr><td><b><?= h(d_br($c['semana_inicio'])) ?></b> a <?= h(date('d/m/Y', strtotime($c['semana_inicio'] . ' +4 day'))) ?></td>
      <td><span class="b b-<?= h($c['status']) ?>"><?= $c['status'] === 'publicado' ? 'Publicado' : 'Rascunho' ?></span></td>
      <td><a class="btn p c" href="<?= url('painel/cardapio/' . (int)$c['id']) ?>">Editar</a>
        <a class="btn p s" target="_blank" href="<?= url('cardapio/' . (int)$c['id']) ?>">Ver / Imprimir</a></td></tr>
    <?php endforeach; ?>
  </table>
</div>
