<?php
namespace App\Services;

use PDO;
use PDOStatement;

/* Conexão única com o SQLite. Cria as tabelas (schema.sql) e carrega os dados de demonstração (seeds.sql) na primeira execução. */
class Database
{
    private static ?PDO $pdo = null;

    public static function pdo(): PDO
    {
        if (self::$pdo) return self::$pdo;
        $cfg = config('database');
        $pdo = new PDO('sqlite:' . $cfg['arquivo']);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        $pdo->exec('PRAGMA foreign_keys = ON');
        self::$pdo = $pdo;
        self::preparar($pdo, $cfg);
        return $pdo;
    }

    private static function preparar(PDO $pdo, array $cfg): void
    {
        $existe = $pdo->query("SELECT COUNT(*) FROM sqlite_master WHERE type='table' AND name='usuario'")->fetchColumn();
        if (!$existe) $pdo->exec(file_get_contents($cfg['schema']));
        self::migrar($pdo);
        if (!empty($cfg['semear']) && (int)$pdo->query('SELECT COUNT(*) FROM usuario')->fetchColumn() === 0) {
            $pdo->exec(file_get_contents($cfg['seeds']));
        }
    }

    /* ajustes de bancos criados em versões anteriores (idempotente) */
    private static function migrar(PDO $pdo): void
    {
        $pdo->exec("UPDATE OR IGNORE cardapio_dia SET refeicao = CASE refeicao WHEN 'lanche_manha' THEN 'manha' WHEN 'almoco' THEN 'tarde' WHEN 'lanche_tarde' THEN 'noite' END
                    WHERE refeicao IN ('lanche_manha','almoco','lanche_tarde')");
    }

    public static function q(string $sql, array $args = []): PDOStatement
    {
        $s = self::pdo()->prepare($sql);
        $s->execute($args);
        return $s;
    }

    public static function ultimoId(): int { return (int)self::pdo()->lastInsertId(); }
}
