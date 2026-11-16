<div class="card h"><h2><?= $id ? 'Editar usuário' : 'Novo usuário' ?></h2>
  <form method="post" action="<?= url('painel/usuarios') ?>" autocomplete="off">
    <?= csrf_campo() ?><input type="hidden" name="id" value="<?= (int)$id ?>">
    <div class="g2">
      <div><label>Nome</label><input name="nome" required value="<?= h($x['nome']) ?>"></div>
      <div><label>E-mail (login)</label><input type="email" name="email" required value="<?= h($x['email']) ?>"></div>
    </div>
    <div class="g3">
      <div><label>Perfil</label>
        <select name="perfil"><?= opts(['editor' => 'Editor – cria e edita as próprias publicações', 'revisor' => 'Revisor – aprova e devolve', 'administrador' => 'Administrador – tudo'], $x['perfil']) ?></select></div>
      <div><label><?= $id ? 'Nova senha (deixe vazio para manter)' : 'Senha' ?></label><input type="password" name="senha" autocomplete="new-password"></div>
      <div><label>&nbsp;</label><label class="chk"><input type="checkbox" name="ativo" value="1" <?= $x['ativo'] ? 'checked' : '' ?>> Usuário ativo</label></div>
    </div>
    <div class="acoes"><button>Salvar usuário</button><?php if ($id): ?><a class="btn c" href="<?= url('painel/usuarios') ?>">Cancelar</a><?php endif; ?></div>
  </form>
</div>
<div class="card">
  <table>
    <tr><th>Nome</th><th>E-mail</th><th>Perfil</th><th>Status</th><th></th></tr>
    <?php foreach ($usuarios as $u): ?>
    <tr><td><?= h($u['nome']) ?></td><td><?= h($u['email']) ?></td><td><?= h($u['perfil']) ?></td>
      <td><?= $u['ativo'] ? '<span class="b b-aprovado">Ativo</span>' : '<span class="b b-arquivado">Inativo</span>' ?></td>
      <td><a class="btn p c" href="<?= url('painel/usuarios/' . (int)$u['id'] . '/editar') ?>">Editar</a></td></tr>
    <?php endforeach; ?>
  </table>
</div>
