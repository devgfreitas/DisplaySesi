<?php
session_start();
if (empty($_SESSION['adm'])) {
    http_response_code(403);
    exit('<meta charset="UTF-8"><p style="font-family:Arial;padding:2rem">Acesso restrito. Entre como administrador em <a href="index.php">index.php</a> (tecla L).</p>');
}

const ARQ_DADOS = __DIR__ . '/data.json';
const PASTA_IMG = __DIR__ . '/uploads';

/* categoria => [rótulo, limite de itens (1 = substitui), título obrigatório, usa data] */
$CATS = [
    'noticia'        => ['Notícia',         3, 'Título da notícia',   false],
    'cardapio'       => ['Cardápio',        1, 'Título (ex.: Cardápio da semana)', false],
    'evento'         => ['Evento',          5, 'Nome do evento',      true],
    'comunicado'     => ['Comunicado',      3, 'Título do comunicado', false],
    'aniversariante' => ['Aniversariante',  0, 'Nome',                true],  // 0 = ilimitado
];

if (!is_dir(PASTA_IMG)) mkdir(PASTA_IMG, 0755, true);
$dados = is_file(ARQ_DADOS) ? (json_decode(file_get_contents(ARQ_DADOS), true) ?: []) : [];
if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(16));

function e($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
function salvar($d) { file_put_contents(ARQ_DADOS, json_encode($d, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT), LOCK_EX); }
function apagarImg($n) { if ($n && is_file(PASTA_IMG . '/' . basename($n))) unlink(PASTA_IMG . '/' . basename($n)); }

$msg = ''; $erro = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!hash_equals($_SESSION['csrf'], $_POST['csrf'] ?? '')) {
        $erro = 'Sessão inválida. Recarregue a página.';
    } elseif (($_POST['acao'] ?? '') === 'apagar') {
        $c = $_POST['cat'] ?? ''; $k = (int)($_POST['idx'] ?? -1);
        if (isset($CATS[$c])) {
            if ($CATS[$c][1] === 1) { apagarImg($dados[$c]['img'] ?? ''); unset($dados[$c]); }
            elseif (isset($dados[$c][$k])) { apagarImg($dados[$c][$k]['img'] ?? ''); array_splice($dados[$c], $k, 1); }
            salvar($dados); $msg = 'Item removido.';
        }
    } else {
        $cat    = $_POST['categoria'] ?? '';
        $titulo = trim($_POST['titulo'] ?? '');
        $texto  = trim($_POST['texto'] ?? '');
        $data   = trim($_POST['data'] ?? '');

        if (!isset($CATS[$cat])) $erro = 'Escolha uma categoria.';
        elseif ($titulo === '') $erro = 'Preencha o título.';
        else {
            $nome = '';
            $f = $_FILES['imagem'] ?? null;
            if ($f && $f['error'] === UPLOAD_ERR_OK) {
                $info = @getimagesize($f['tmp_name']);
                $ext = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp', 'image/gif' => 'gif'][$info['mime'] ?? ''] ?? null;
                if (!$ext) $erro = 'Envie uma imagem JPG, PNG, WEBP ou GIF.';
                elseif ($f['size'] > 8 * 1024 * 1024) $erro = 'A imagem deve ter no máximo 8 MB.';
                else {
                    $nome = $cat . '_' . bin2hex(random_bytes(6)) . '.' . $ext;
                    move_uploaded_file($f['tmp_name'], PASTA_IMG . '/' . $nome);
                }
            } elseif ($f && $f['error'] !== UPLOAD_ERR_NO_FILE) {
                $erro = 'Falha no envio da imagem (código ' . $f['error'] . ').';
            }

            if (!$erro) {
                $item = ['titulo' => $titulo, 'texto' => $texto, 'data' => $data, 'img' => $nome, 'criado' => date('Y-m-d H:i:s')];
                if ($CATS[$cat][1] === 1) {            // substitui o atual
                    apagarImg($dados[$cat]['img'] ?? '');
                    $dados[$cat] = $item;
                } else {                               // entra no fim; o mais antigo sai
                    $dados[$cat] = $dados[$cat] ?? [];
                    $dados[$cat][] = $item;
                    while ($CATS[$cat][1] > 0 && count($dados[$cat]) > $CATS[$cat][1]) {
                        $velho = array_shift($dados[$cat]);
                        apagarImg($velho['img'] ?? '');
                    }
                }
                salvar($dados);
                $msg = 'Publicado em ' . $CATS[$cat][0] . '. O display atualiza no próximo ciclo.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Sesi · Formulário</title>
<style>
:root{--verm:#E30613;--preto:#171717;--cinza:#F2F2F2}
*{box-sizing:border-box}
body{margin:0;font-family:Arial,Helvetica,sans-serif;background:var(--cinza);color:var(--preto)}
header{background:var(--verm);color:#fff;padding:18px 24px;display:flex;align-items:center;gap:16px}
header b{background:#fff;color:var(--verm);font-style:italic;font-weight:900;font-size:22px;padding:4px 14px;border-radius:8px}
header h1{font-size:20px;margin:0}
main{max-width:760px;margin:0 auto;padding:24px}
form,.lista{background:#fff;border-radius:14px;padding:22px;box-shadow:0 2px 10px rgba(0,0,0,.07);margin-bottom:22px}
label{display:block;font-weight:700;margin:14px 0 6px}
input,select,textarea{width:100%;padding:11px;font:inherit;border:2px solid #d5d5d5;border-radius:9px;background:#fff}
input:focus,select:focus,textarea:focus{outline:none;border-color:var(--verm)}
textarea{min-height:110px;resize:vertical}
button{background:var(--verm);color:#fff;border:0;border-radius:9px;padding:13px 26px;font:700 16px Arial;cursor:pointer;margin-top:18px}
.dica{font-size:13px;color:#666;margin-top:5px}
.ok{background:#e7f6ea;border-left:5px solid #1f9d3a;padding:12px;border-radius:8px;margin-bottom:16px}
.er{background:#fde8ea;border-left:5px solid var(--verm);padding:12px;border-radius:8px;margin-bottom:16px}
h2{font-size:18px;margin:0 0 12px}
.it{display:flex;align-items:center;gap:12px;padding:10px 0;border-top:1px solid #eee}
.it img{width:56px;height:56px;object-fit:cover;border-radius:8px;background:#ddd}
.it span{flex:1}.it small{color:#666;display:block}
.it form{all:unset}
.it button{margin:0;padding:8px 14px;font-size:14px;background:var(--preto)}
.oculto{display:none}
</style>
</head>
<body>
<header><b>SESI</b><h1>Enviar conteúdo para o display</h1></header>
<main>
<?php if ($msg): ?><div class="ok"><?= e($msg) ?></div><?php endif; ?>
<?php if ($erro): ?><div class="er"><?= e($erro) ?></div><?php endif; ?>

<form method="post" enctype="multipart/form-data">
  <input type="hidden" name="csrf" value="<?= e($_SESSION['csrf']) ?>">

  <label for="categoria">Categoria</label>
  <select name="categoria" id="categoria" required>
    <option value="">Selecione…</option>
    <?php foreach ($CATS as $k => $c): ?><option value="<?= $k ?>"><?= e($c[0]) ?></option><?php endforeach; ?>
  </select>
  <div class="dica" id="regra"></div>

  <label for="titulo" id="lbTitulo">Título</label>
  <input type="text" name="titulo" id="titulo" maxlength="120" required>

  <div id="boxTexto">
    <label for="texto">Texto</label>
    <textarea name="texto" id="texto" maxlength="800"></textarea>
    <div class="dica" id="dicaTexto"></div>
  </div>

  <div id="boxData" class="oculto">
    <label for="data" id="lbData">Data</label>
    <input type="date" name="data" id="data">
  </div>

  <label for="imagem">Imagem</label>
  <input type="file" name="imagem" id="imagem" accept="image/jpeg,image/png,image/webp,image/gif">
  <div class="dica">JPG, PNG, WEBP ou GIF, até 8 MB.</div>

  <button type="submit">Publicar no display</button>
</form>

<div class="lista">
  <h2>Conteúdo atual no display</h2>
  <?php $vazio = true; foreach ($CATS as $k => $c):
      $itens = $c[1] === 1 ? (isset($dados[$k]) ? [$dados[$k]] : []) : ($dados[$k] ?? []);
      foreach ($itens as $idx => $it): $vazio = false; ?>
  <div class="it">
    <?php if (!empty($it['img'])): ?><img src="uploads/<?= rawurlencode($it['img']) ?>" alt=""><?php else: ?><img alt=""><?php endif; ?>
    <span><?= e($it['titulo']) ?><small><?= e($c[0]) ?></small></span>
    <form method="post" onsubmit="return confirm('Remover este item?')">
      <input type="hidden" name="csrf" value="<?= e($_SESSION['csrf']) ?>">
      <input type="hidden" name="acao" value="apagar">
      <input type="hidden" name="cat" value="<?= $k ?>">
      <input type="hidden" name="idx" value="<?= $idx ?>">
      <button type="submit">Remover</button>
    </form>
  </div>
  <?php endforeach; endforeach; if ($vazio): ?><p>Nada publicado ainda.</p><?php endif; ?>
</div>
</main>

<script>
const regras = {
  noticia:        ['Substitui a notícia mais antiga (o display mostra as 3 mais recentes).', 'Título da notícia', 'Resumo (opcional)', false, ''],
  cardapio:       ['Substitui o cardápio atual (imagem e texto).', 'Título (ex.: Cardápio da semana)', 'Escreva uma refeição por linha, ex.: Seg – arroz, feijão, frango', false, ''],
  evento:         ['Entra na lista de eventos; o mais antigo sai quando passar de 5.', 'Nome do evento', 'Descrição (opcional)', true, 'Data do evento'],
  comunicado:     ['Substitui o comunicado mais antigo (o display mostra os 3 mais recentes).', 'Título do comunicado', 'Texto do comunicado', false, ''],
  aniversariante: ['Sem limite: todos entram e passam em carrossel no display, em ordem de dia do mês.', 'Nome', 'Observação (opcional)', true, 'Data de nascimento']
};
const sel = document.getElementById('categoria');
sel.addEventListener('change', () => {
  const r = regras[sel.value];
  document.getElementById('regra').textContent = r ? r[0] : '';
  if (!r) return;
  document.getElementById('lbTitulo').textContent = r[1];
  document.getElementById('dicaTexto').textContent = r[2];
  document.getElementById('boxData').classList.toggle('oculto', !r[3]);
  document.getElementById('lbData').textContent = r[4];
});
</script>
</body>
</html>
