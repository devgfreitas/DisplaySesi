<?php
namespace App\Services;

use App\Models\Aniversariante;
use App\Models\Turma;
use RuntimeException;

/* Importação controlada: lê um CSV (nome;turma;nascimento) e cadastra todos SEM autorização de exibição. */
class ImportadorAniversariantes
{
    public const MAX_LINHAS = 500;

    /* devolve [importados, ignorados] */
    public static function importar(?array $arquivo): array
    {
        if (!$arquivo || $arquivo['error'] !== UPLOAD_ERR_OK || $arquivo['size'] > 1024 * 1024) {
            throw new RuntimeException('Envie um arquivo CSV válido de até 1 MB.');
        }
        $linhas = preg_split('/\R/u', (string)file_get_contents($arquivo['tmp_name']));
        $turmas = Turma::mapaPorNome();
        $ok = 0; $ign = 0;
        foreach (array_slice($linhas, 0, self::MAX_LINHAS + 1) as $i => $l) {
            $l = trim($l);
            if ($l === '') continue;
            $c = str_getcsv($l, substr_count($l, ';') >= substr_count($l, ',') ? ';' : ',');
            if (count($c) < 3) { $ign++; continue; }
            [$nome, $turma, $dn] = [trim($c[0]), trim($c[1]), trim($c[2])];
            if (preg_match('#^(\d{1,2})/(\d{1,2})/(\d{4})$#', $dn, $m)) $dn = sprintf('%04d-%02d-%02d', $m[3], $m[2], $m[1]);
            if ($i === 0 && !strtotime($dn)) continue;                       // cabeçalho
            if ($nome === '' || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $dn) || !strtotime($dn)) { $ign++; continue; }
            Aniversariante::criar($nome, $turmas[mb_strtolower($turma)] ?? null, $dn, null);
            $ok++;
        }
        return [$ok, $ign];
    }
}
