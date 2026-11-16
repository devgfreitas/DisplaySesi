<?php
/*
 * Mapa de rotas. Middlewares: 'auth' (logado), 'perfil:revisor' (revisor ou administrador),
 * 'perfil' (só administrador). Todo POST passa automaticamente pela verificação CSRF.
 */
use App\Services\Router;

return function (Router $r): void {
    /* ---------- público ---------- */
    $r->get('/',            'TvController@index');                 // TV da entrada
    $r->get('/tv/versao',   'TvController@versao');                // a TV pergunta se há novidade
    $r->get('/consulta',    'ConsultaController@index');           // consulta por turma
    $r->get('/cardapio',    'CardapioController@publico');         // cardápio (tela e impressão)
    $r->get('/cardapio/{id}', 'CardapioController@publico');

    /* ---------- acesso ---------- */
    $r->post('/login',      'AuthController@login');
    $r->get('/sair',        'AuthController@sair');

    /* ---------- painel (equipe) ---------- */
    $r->get('/painel',      'PainelController@resumo', ['auth']);

    // publicações
    $r->get('/painel/publicacoes',                'PublicacaoController@lista',   ['auth']);
    $r->get('/painel/publicacoes/nova',           'PublicacaoController@nova',    ['auth']);
    $r->get('/painel/publicacoes/{id}/editar',    'PublicacaoController@editar',  ['auth']);
    $r->post('/painel/publicacoes',               'PublicacaoController@salvar',  ['auth']);
    $r->post('/painel/publicacoes/{id}/enviar',   'PublicacaoController@enviar',  ['auth']);
    $r->post('/painel/publicacoes/{id}/arquivar', 'PublicacaoController@arquivar', ['auth']);
    $r->post('/painel/publicacoes/{id}/aprovar',  'PublicacaoController@aprovar', ['auth', 'perfil:revisor']);
    $r->post('/painel/publicacoes/{id}/rejeitar', 'PublicacaoController@rejeitar', ['auth', 'perfil:revisor']);
    $r->get('/painel/revisao',                    'PublicacaoController@fila',    ['auth', 'perfil:revisor']);

    // cardápio semanal
    $r->get('/painel/cardapio',                   'CardapioController@lista',        ['auth']);
    $r->post('/painel/cardapio/nova',             'CardapioController@nova',         ['auth']);
    $r->get('/painel/cardapio/{id}',              'CardapioController@editar',       ['auth']);
    $r->post('/painel/cardapio/{id}',             'CardapioController@salvar',       ['auth']);
    $r->post('/painel/cardapio/{id}/despublicar', 'CardapioController@despublicar',  ['auth', 'perfil:revisor']);
    $r->post('/painel/cardapio/{id}/excluir',     'CardapioController@excluir',      ['auth', 'perfil:revisor']);

    // calendário escolar
    $r->get('/painel/eventos',                    'EventoController@index',   ['auth']);
    $r->get('/painel/eventos/{id}/editar',        'EventoController@index',   ['auth']);
    $r->post('/painel/eventos',                   'EventoController@salvar',  ['auth']);
    $r->post('/painel/eventos/{id}/excluir',      'EventoController@excluir', ['auth', 'perfil:revisor']);

    // aniversariantes (a autorização para exibir é decisão do administrador)
    $r->get('/painel/aniversariantes',                  'AniversarianteController@index',     ['auth']);
    $r->post('/painel/aniversariantes',                 'AniversarianteController@criar',     ['auth']);
    $r->post('/painel/aniversariantes/importar',        'AniversarianteController@importar',  ['auth']);
    $r->post('/painel/aniversariantes/autorizar',       'AniversarianteController@autorizar', ['auth', 'perfil']);
    $r->post('/painel/aniversariantes/revogar',         'AniversarianteController@revogar',   ['auth', 'perfil']);
    $r->post('/painel/aniversariantes/{id}/excluir',    'AniversarianteController@excluir',   ['auth']);

    // administração (somente administrador)
    $r->get('/painel/turmas',                     'TurmaController@index',    ['auth', 'perfil']);
    $r->post('/painel/turmas',                    'TurmaController@criar',    ['auth', 'perfil']);
    $r->post('/painel/turmas/{id}/alternar',      'TurmaController@alternar', ['auth', 'perfil']);
    $r->get('/painel/usuarios',                   'UsuarioController@index',  ['auth', 'perfil']);
    $r->get('/painel/usuarios/{id}/editar',       'UsuarioController@index',  ['auth', 'perfil']);
    $r->post('/painel/usuarios',                  'UsuarioController@salvar', ['auth', 'perfil']);
};
