<div class="card h">
  <h2>Escolha a turma</h2>
  <form method="get" action="<?= url('consulta') ?>" class="g3 fim">
    <div><label>Turma</label><select name="turma" required><option value="">Selecione…</option><?= opts($turmas, $tid) ?></select></div>
    <div><label>Eu sou</label><select name="perfil"><?= opts(['visitante' => 'Visitante', 'comunidade' => 'Aluno ou responsável'], $perfil) ?></select></div>
    <div><button>Consultar</button></div>
  </form>
  <div class="dica">Recados individuais, notas, faltas e contatos nunca aparecem aqui. O perfil define se conteúdos internos também são mostrados.</div>
</div>

<?php if ($tid): ?>
  <h2><?= h($turmas[$tid]) ?></h2>
  <?php $algum = false; foreach ($ordem as $tp => $rot): if (empty($grupos[$tp])) continue; $algum = true; ?>
    <h3><?= h($rot) ?></h3>
    <div class="lista-c mb22">
      <?php foreach ($grupos[$tp] as $r): ?>
      <div class="item">
        <div class="t"><span><?= h($rot) ?><?= $r['disciplina'] ? ' · ' . h($r['disciplina']) : '' ?></span>
          <span><?= $r['prazo'] ? 'Prazo: ' . h(d_br($r['prazo'])) : ($r['data_ref'] ? h(d_br($r['data_ref'])) : '') ?></span></div>
        <div class="m"><h3><?= h($r['titulo']) ?></h3><p><?= nl2br(h($r['descricao'])) ?></p>
          <small>Publicado em <?= h(d_br($r['inicio_exibicao'] ?: $r['criado_em'])) ?></small></div>
      </div>
      <?php endforeach; ?>
    </div>
  <?php endforeach; if (!$algum): ?><div class="card"><p>Nenhum conteúdo para esta turma no momento.</p></div><?php endif; ?>
<?php endif; ?>

<?php if ($eventos): ?>
<div class="card h"><h3>Próximos eventos da escola</h3>
  <table><?php foreach ($eventos as $e): ?><tr><td class="w110"><b><?= h(d_br($e['data_inicio'])) ?></b></td><td><?= h($e['titulo']) ?></td></tr><?php endforeach; ?></table>
</div>
<?php endif; ?>
