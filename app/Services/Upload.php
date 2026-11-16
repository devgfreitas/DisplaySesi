<?php
namespace App\Services;

use RuntimeException;

/* Recebe imagens com validação do tipo real (não confia na extensão) e do tamanho. */
class Upload
{
    public static function diretorio(): string { return BASE_PATH . '/public/uploads'; }

    /* devolve o nome salvo, ou '' se nenhum arquivo foi enviado */
    public static function salvar(string $campo, string $prefixo): string
    {
        $f = $_FILES[$campo] ?? null;
        if (!$f || $f['error'] === UPLOAD_ERR_NO_FILE) return '';
        if ($f['error'] !== UPLOAD_ERR_OK) throw new RuntimeException('Falha no envio da imagem.');
        if ($f['size'] > config('app.upload.max_mb') * 1024 * 1024) throw new RuntimeException('A imagem deve ter no máximo ' . config('app.upload.max_mb') . ' MB.');
        $info = @getimagesize($f['tmp_name']);
        $ext = config('app.upload.mimes')[$info['mime'] ?? ''] ?? null;
        if (!$ext) throw new RuntimeException('Envie uma imagem JPG, PNG, WEBP ou GIF.');
        if (!is_dir(self::diretorio())) mkdir(self::diretorio(), 0755, true);
        $nome = $prefixo . '_' . bin2hex(random_bytes(6)) . '.' . $ext;
        move_uploaded_file($f['tmp_name'], self::diretorio() . '/' . $nome);
        return $nome;
    }

    public static function apagar(?string $nome): void
    {
        if (!$nome) return;
        $arq = self::diretorio() . '/' . basename($nome);
        if (is_file($arq)) @unlink($arq);
    }
}
