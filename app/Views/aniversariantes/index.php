<div class="cols">
  <div class="card h"><h3>Cadastrar aniversariante</h3>
    <form method="post" action="<?= url('painel/aniversariantes') ?>" enctype="multipart/form-data">
      <?= csrf_campo() ?>
      <label>Nome</label><input name="nome" required>
      <div class="g2">
        <div><label>Nascimento</label><input type="date" name="data_nascimento" required></div>
        <div><label>Turma</label><select name="turma_id"><option value="">—</option><?= opts($turmas) ?></select></div>
      </div>
      <label>Foto (opcional)</label><input type="file" name="foto" accept="image/*">
      <div class="acoes"><button>Cadastrar</button></div>
    </form>
  </div>
  <div class="card h"><h3>Importação controlada (CSV)</h3>
    <form method="post" action="<?= url('painel/aniversariantes/importar') ?>" enctype="multipart/form-data">
      <?= csrf_campo() ?>
      <label>Arquivo .csv</label><input type="file" name="csv" accept=".csv,text/csv" required>
      <div class="dica">Colunas: <b>nome;turma;nascimento</b> (dd/mm/aaaa). Até 500 linhas. Todos entram <b>sem autorização</b> e só vão à TV depois de autorizados abaixo.</div>
      <div class="acoes"><button class="s">Importar</button></div>
    </form>
  </div>
</div>

<form method="post" action="<?= url('painel/aniversariantes/autorizar') ?>">
  <?= csrf_campo() ?>
  <div class="card"><h3>Aniversariantes por mês</h3>
    <?php if (pode()): ?>
      <div class="aviso-tv mb12">
        <label class="chk m0"><input type="checkbox" name="politica" value="1"> Confirmo que há autorização dos responsáveis e que a exibição segue a política institucional da escola.</label>
        <div class="acoes mt10">
          <button class="p">Autorizar marcados</button>
          <button class="p c" formaction="<?= url('painel/aniversariantes/revogar') ?>">Revogar marcados</button>
        </div>
      </div>
    <?php else: ?><p class="dica">Somente o administrador registra a autorização para exibição na TV.</p><?php endif; ?>

    <?php $mesAt = 0; foreach ($rows as $a):
        $m = (int)date('n', strtotime($a['data_nascimento']));
        if ($m !== $mesAt) { if ($mesAt) echo '</table>'; echo '<div class="mes">' . h($meses[$m]) . '</div><table>'; $mesAt = $m; } ?>
      <tr>
        <td class="w30"><?php if (pode()): ?><input type="checkbox" name="sel[]" value="<?= (int)$a['id'] ?>"><?php endif; ?></td>
        <td><b><?= h(date('d/m', strtotime($a['data_nascimento']))) ?></b></td>
        <td><?= h($a['nome']) ?></td>
        <td><?= h($a['turma'] ?? '') ?></td>
        <td><?= $a['autorizado'] ? '<span class="b b-aprovado">Autorizado</span>' : '<span class="b b-rascunho">Sem autorização</span>' ?><?= $a['obs_autorizacao'] ? '<br><small>' . h($a['obs_autorizacao']) . '</small>' : '' ?></td>
        <td><button class="p s" formaction="<?= url('painel/aniversariantes/' . (int)$a['id'] . '/excluir') ?>" data-confirm="Remover este aniversariante?">Remover</button></td>
      </tr>
    <?php endforeach; if ($mesAt) echo '</table>'; else echo '<p>Nenhum aniversariante cadastrado.</p>'; ?>
  </div>
</form>
