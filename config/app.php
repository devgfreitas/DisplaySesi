<?php
/* Configurações gerais, rótulos e regras do sistema. */
return [
    'nome'     => 'Sesi',
    'timezone' => 'America/Sao_Paulo',
    'debug'    => false,   // true mostra detalhes de erros na tela (use só em desenvolvimento)

    'tv' => [
        'tempo_slide'         => 14,     // segundos por tela
        'ms_carrossel_home'   => 4000,   // tempo de cada página do carrossel da tela inicial
        'ms_carrossel_festa'  => 6000,   // tempo de cada página na tela de aniversariantes
        'max_festa_seg'       => 120,    // tempo máximo da tela de aniversariantes por ciclo
        'polling_seg'         => 20,     // de quanto em quanto tempo a TV confere se há conteúdo novo
        'tipos_permitidos'    => ['comunicado', 'aviso', 'noticia'],   // únicos tipos que podem ir à TV pública
    ],

    'tipos' => [
        'comunicado' => 'Comunicado', 'aviso' => 'Aviso', 'noticia' => 'Notícia', 'recado' => 'Recado da turma',
        'prova' => 'Prova', 'tarefa' => 'Tarefa', 'recado_individual' => 'Recado individual',
    ],
    'status' => [
        'rascunho' => 'Rascunho', 'em_revisao' => 'Em revisão', 'aprovado' => 'Aprovado',
        'rejeitado' => 'Rejeitado', 'arquivado' => 'Arquivado',
    ],
    'visibilidade' => [
        'publico' => 'Público', 'interno' => 'Interno (comunidade escolar)', 'privado' => 'Privado (só equipe)',
    ],
    'refeicoes'    => ['manha' => 'Manhã', 'tarde' => 'Tarde', 'noite' => 'Noite'],
    'dias'         => [1 => 'Segunda', 2 => 'Terça', 3 => 'Quarta', 4 => 'Quinta', 5 => 'Sexta'],
    'tipos_evento' => ['evento' => 'Evento', 'data_comemorativa' => 'Data comemorativa', 'reuniao' => 'Reunião', 'atividade' => 'Atividade'],
    'perfis'       => ['editor' => 'Editor', 'revisor' => 'Revisor', 'administrador' => 'Administrador'],

    'upload' => [
        'max_mb' => 8,
        'mimes'  => ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp', 'image/gif' => 'gif'],
    ],
];
