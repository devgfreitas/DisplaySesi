<?php
namespace App\Services;

class Csrf
{
    public static function token(): string
    {
        if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(16));
        return $_SESSION['csrf'];
    }

    public static function campo(): string { return '<input type="hidden" name="csrf" value="' . self::token() . '">'; }

    public static function valido(): bool { return hash_equals(self::token(), (string)($_POST['csrf'] ?? '')); }
}
