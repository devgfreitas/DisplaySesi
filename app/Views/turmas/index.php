<div class="card h"><h2>Turmas</h2>
  <form method="post" action="<?= url('painel/turmas') ?>" class="g3 fim">
    <?= csrf_campo() ?>
    <div><label>Nova turma</label><input name="nome" required placeholder="ex.: 2º EM B"></div>
    <div><button>Adicionar</button></div>
  </form>
</div>
<div class="card">
  <table>
    <tr><th>Turma</th><th>Publicações</th><th>Status</th><th></th></tr>
    <?php foreach ($turmas as $t): ?>
    <tr><td><?= h($t['nome']) ?></td><td><?= (int)$t['n'] ?></td>
      <td><?= $t['ativo'] ? '<span class="b b-aprovado">Ativa</span>' : '<span class="b b-arquivado">Inativa</span>' ?></td>
      <td><?= form_acao('painel/turmas/' . (int)$t['id'] . '/alternar', $t['ativo'] ? 'Desativar' : 'Ativar', 'c') ?></td></tr>
    <?php endforeach; ?>
  </table>
</div>
