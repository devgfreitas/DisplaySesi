<div class="card h">
  <h2><?= $id ? 'Editar publicação' : 'Nova publicação' ?></h2>
  <form method="post" action="<?= url('painel/publicacoes') ?>" enctype="multipart/form-data">
    <?= csrf_campo() ?><input type="hidden" name="id" value="<?= (int)$id ?>">
    <div class="g2">
      <div><label for="tipo">Tipo</label><select name="tipo" id="tipo"><?= opts(config('app.tipos'), $r['tipo']) ?></select></div>
      <div><label>Título</label><input name="titulo" maxlength="120" required value="<?= h($r['titulo']) ?>"></div>
    </div>
    <label>Descrição</label><textarea name="descricao" maxlength="1500"><?= h($r['descricao']) ?></textarea>
    <div class="g3" id="boxProva">
      <div><label>Disciplina</label><input name="disciplina" value="<?= h($r['disciplina']) ?>"></div>
      <div><label>Data (prova / recado)</label><input type="date" name="data_ref" value="<?= h($r['data_ref']) ?>"></div>
      <div><label>Prazo (tarefa)</label><input type="date" name="prazo" value="<?= h($r['prazo']) ?>"></div>
    </div>

    <label>Público-alvo</label>
    <div class="chks">
      <label class="chk"><input type="radio" name="publico_alvo" value="geral" <?= $r['publico_alvo'] === 'geral' ? 'checked' : '' ?>> Geral (toda a escola)</label>
      <label class="chk"><input type="radio" name="publico_alvo" value="turmas" <?= $r['publico_alvo'] === 'turmas' ? 'checked' : '' ?>> Turmas específicas</label>
    </div>
    <div class="chks" id="boxTurmas">
      <?php foreach ($turmas as $tid => $tnome): ?>
        <label class="chk"><input type="checkbox" name="turmas[]" value="<?= (int)$tid ?>" <?= in_array((int)$tid, $sel, true) ? 'checked' : '' ?>> <?= h($tnome) ?></label>
      <?php endforeach; ?>
    </div>

    <div class="g2">
      <div><label>Visibilidade</label><select name="visibilidade"><?= opts(config('app.visibilidade'), $r['visibilidade']) ?></select>
        <div class="dica">Privado nunca aparece na consulta nem na TV.</div></div>
      <div><label>Evento relacionado (opcional)</label><select name="evento_id"><option value="">—</option><?= opts($eventos, $r['evento_id']) ?></select></div>
    </div>
    <div class="g2">
      <div><label>Início da exibição</label><input type="datetime-local" name="inicio" value="<?= h(dt_local($r['inicio_exibicao'])) ?>"><div class="dica">Vazio = imediatamente. Data futura = agendado.</div></div>
      <div><label>Fim da exibição (validade)</label><input type="datetime-local" name="fim" value="<?= h(dt_local($r['fim_exibicao'])) ?>"><div class="dica">Depois disso sai da exibição e fica no histórico.</div></div>
    </div>

    <div class="aviso-tv">
      <label class="chk mt0"><input type="checkbox" name="exibir_tv" value="1" id="tv" <?= $r['exibir_tv'] ? 'checked' : '' ?>> <b>Exibir na TV pública da entrada</b></label>
      <div class="dica">Só vale para comunicado, aviso e notícia, com visibilidade "Público", geral e sem dados de alunos, e só aparece depois de revisado e aprovado.</div>
      <div class="g2" id="boxTv">
        <div><label>Prioridade</label><select name="prioridade"><?= opts([1 => 'Normal', 2 => 'Alta', 3 => 'Urgente'], $r['prioridade']) ?></select></div>
        <div><label>Duração na tela (segundos)</label><input type="number" name="duracao_seg" min="5" max="120" value="<?= (int)$r['duracao_seg'] ?>"></div>
      </div>
    </div>

    <label>Imagem</label><input type="file" name="imagem" accept="image/jpeg,image/png,image/webp,image/gif">
    <?php if ($r['imagem']): ?><div class="dica">Imagem atual: <?= h($r['imagem']) ?> (envie outra para substituir)</div><?php endif; ?>

    <div class="acoes">
      <button name="destino" value="rascunho" class="c">Salvar rascunho</button>
      <button name="destino" value="revisao" class="s">Salvar e enviar para revisão</button>
      <?php if (pode('revisor')): ?><button name="destino" value="aprovar">Salvar e aprovar</button><?php endif; ?>
      <a class="btn c" href="<?= url('painel/publicacoes') ?>">Cancelar</a>
    </div>
  </form>
</div>
