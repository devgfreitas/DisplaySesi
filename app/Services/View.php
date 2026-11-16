<?php
namespace App\Services;

class View
{
    /* Renderiza app/Views/{view}.php dentro de app/Views/layouts/{layout}.php (ou sem layout, se null). */
    public static function render(string $view, array $dados = [], ?string $layout = 'painel'): void
    {
        extract($dados, EXTR_SKIP);
        ob_start();
        require BASE_PATH . '/app/Views/' . $view . '.php';
        $conteudo = ob_get_clean();
        if ($layout) require BASE_PATH . '/app/Views/layouts/' . $layout . '.php';
        else echo $conteudo;
    }
}
