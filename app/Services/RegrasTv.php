<?php
namespace App\Services;

/*
 * Regras que impedem conteúdo privado de chegar à TV pública.
 * Dados de alunos, notas, faltas, contatos e recados individuais NUNCA podem passar por aqui.
 */
class RegrasTv
{
    /* detecta dados que não podem aparecer em tela pública */
    public static function conteudoSensivel(string $texto): ?string
    {
        if (preg_match('/\b\d{3}\.?\d{3}\.?\d{3}-?\d{2}\b/u', $texto)) return 'contém CPF';
        if (preg_match('/\(?\b\d{2}\)?[\s.-]?9?\d{4}[\s.-]?\d{4}\b/u', $texto)) return 'contém telefone';
        if (preg_match('/[\w.+-]+@[\w-]+\.[\w.]+/u', $texto)) return 'contém e-mail';
        if (preg_match('/\b(notas?|faltas?|boletim|laudo|cpf|telefone|celular|whatsapp)\b/iu', $texto, $m)) return 'contém termo sensível ("' . $m[1] . '")';
        return null;
    }

    /* devolve o motivo do bloqueio, ou null se pode ir à TV. $d = tipo, visibilidade, publico_alvo, texto */
    public static function validar(array $d): ?string
    {
        if (!in_array($d['tipo'], config('app.tv.tipos_permitidos'), true)) {
            return 'o tipo "' . config('app.tipos.' . $d['tipo'], $d['tipo']) . '" nunca vai para a TV (só comunicado, aviso e notícia).';
        }
        if ($d['visibilidade'] !== 'publico') return 'a visibilidade precisa ser "Público".';
        if ($d['publico_alvo'] !== 'geral') return 'conteúdo vinculado a turmas não pode ir para a TV pública.';
        if ($m = self::conteudoSensivel($d['texto'])) return 'o texto ' . $m . '. Dados de alunos, notas, faltas e contatos não podem aparecer na TV.';
        return null;
    }

    public static function validarPublicacao(array $p): ?string
    {
        return self::validar([
            'tipo' => $p['tipo'], 'visibilidade' => $p['visibilidade'], 'publico_alvo' => $p['publico_alvo'],
            'texto' => $p['titulo'] . ' ' . $p['descricao'] . ' ' . $p['disciplina'],
        ]);
    }
}
