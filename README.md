# Central de Conteúdo Sesi

Sistema web em PHP para organizar recados, provas, tarefas, cardápio, eventos e avisos da escola, com publicação segmentada por turma e uma **TV da entrada** em tela cheia, sem partes clicáveis, que mostra só conteúdo público e revisado.

Projeto final – Mostra de Tecnologia 2026 · SESI/SENAI · Técnico em Desenvolvimento de Sistemas.

## Requisitos

- PHP 7.4 ou superior com as extensões `pdo_sqlite` e `mbstring` (já vêm no XAMPP)
- Composer é **opcional** (só para rodar os testes). Sem ele o sistema carrega as classes sozinho.

## Como executar

**Servidor embutido do PHP (mais simples)**

```
cd projeto
php -S localhost:8000 -t public
```

Abra `http://localhost:8000`. Para a TV, abra a mesma página no navegador da TV e aperte **F** para tela cheia.

**XAMPP / Apache**

Copie a pasta `projeto` para `htdocs` e abra `http://localhost/projeto/`. O `.htaccess` da raiz encaminha tudo para `public/`, então `app`, `config`, `database` e `storage` não ficam acessíveis pelo navegador. Se for publicar em um servidor, aponte o *DocumentRoot* diretamente para `public/`.

A pasta `database/` e `public/uploads/` precisam de permissão de escrita.

## Primeiro acesso

Na primeira execução o banco SQLite (`database/central.sqlite`) é criado a partir de `database/schema.sql` e preenchido com dados de demonstração (`database/seeds.sql`).

| Perfil | E-mail | Senha |
|---|---|---|
| Administrador | admin@sesi.local | admin123 |
| Revisor | revisor@sesi.local | revisor123 |
| Editor | editor@sesi.local | editor123 |

**Troque as senhas** (aba Usuários) antes de usar de verdade. Para começar sem dados de exemplo, defina `'semear' => false` em `config/database.php` antes da primeira execução e crie o primeiro administrador diretamente no banco.

Na TV, aperte **L** (ou abra `/?login=1`) para entrar. O painel fica em `/painel`.

## Estrutura

```
projeto/
├── app/
│   ├── Controllers/   recebem a requisição e chamam models e views
│   ├── Models/        consultas ao banco (Publicacao, Evento, Cardapio, Aniversariante, Turma, Usuario)
│   ├── Views/         telas PHP (layouts, tv, painel, publicacoes, cardapio, eventos, ...)
│   ├── Services/      Database, Auth, Csrf, Upload, Router, View, RegrasTv, TvService, ImportadorAniversariantes
│   ├── Middleware/    AuthMiddleware, CsrfMiddleware, PerfilMiddleware
│   └── helpers.php    funções de apoio (config, url, datas, componentes)
├── config/            app.php (rótulos, tempos, regras) e database.php
├── public/            único diretório exposto ao navegador
│   ├── index.php      front controller
│   ├── assets/css     tv.css, painel.css
│   ├── assets/js      tv.js, painel.js
│   └── uploads/       imagens enviadas (não executa scripts)
├── routes/web.php     mapa de rotas e middlewares
├── database/          schema.sql e seeds.sql
├── storage/logs/      app.log (erros)
└── tests/             PHPUnit
```

## Rotas principais

| Rota | O que faz | Acesso |
|---|---|---|
| `/` | TV da entrada (rotação automática) | Público |
| `/tv/versao` | A TV pergunta se há conteúdo novo | Público |
| `/consulta` | Provas, tarefas e recados por turma | Público |
| `/cardapio` | Cardápio na tela e para impressão (A4) | Público |
| `/painel` | Indicadores e resumo | Equipe |
| `/painel/publicacoes` | Criar, editar, buscar, enviar para revisão, arquivar | Equipe |
| `/painel/revisao` | Aprovar ou devolver com comentário | Revisor e administrador |
| `/painel/cardapio`, `/painel/eventos`, `/painel/aniversariantes` | Cadastros | Equipe |
| `/painel/turmas`, `/painel/usuarios` | Administração | Administrador |

## Perfis

- **Editor:** cria e edita as próprias publicações e envia para revisão. Nada que ele faça vai à TV ou à consulta sem aprovação.
- **Revisor:** vê todas as publicações, aprova ou devolve com comentário, publica cardápios e exclui eventos.
- **Administrador:** tudo isso, mais usuários, turmas e a **autorização de aniversariantes**.

Editar uma publicação já aprovada a devolve para revisão.

## Regras da TV pública

Um conteúdo só aparece na TV se **todas** as condições forem verdadeiras:

1. tipo comunicado, aviso ou notícia (recados, provas, tarefas e recados individuais nunca vão);
2. visibilidade **Público** e público-alvo **Geral** (nada vinculado a turma);
3. marcado explicitamente como "Exibir na TV", com data de validade;
4. status **Aprovado** por revisor ou administrador;
5. dentro do período de exibição (conteúdo expirado sai sozinho e fica no histórico);
6. texto sem CPF, telefone, e-mail ou termos como "notas", "faltas" e "boletim".

A regra é aplicada ao salvar, ao aprovar e de novo ao exibir (`app/Services/RegrasTv.php` e `TvService.php`). O filtro de palavras é uma proteção extra e não substitui a revisão humana. Aniversariantes só aparecem com autorização registrada pelo administrador.

## Demonstração sugerida

1. Em `/consulta`, escolha a turma "1º EM A" e veja provas e tarefas fictícias.
2. Entre como revisor, crie um **aviso** público com "Exibir na TV" e validade, e clique em "Salvar e aprovar". Em até 20 segundos o novo card aparece na TV.
3. Em `/painel/cardapio`, abra a semana e clique em "Visualizar / Imprimir".
4. Tente enviar para a TV o "Recado individual (exemplo privado)", ou um aviso com um telefone no texto: o sistema bloqueia e explica o motivo.

## Testes

```
composer install
composer test
```

## Configuração

Tempos da TV, rótulos, tipos permitidos e limites de upload ficam em `config/app.php`. Erros são gravados em `storage/logs/app.log`.

## Segurança

Consultas com PDO e parâmetros, senhas com `password_hash`, token CSRF em todo POST, escape de saída (`h()`), validação do tipo real das imagens, pasta `uploads` sem execução de scripts e banco fora da pasta pública.

## Limitações conhecidas

- A escolha "Visitante" ou "Aluno ou responsável" na consulta é um filtro de tela, não um login. Para restringir de verdade, é preciso criar contas.
- O relatório de visualizações conta apenas a área de consulta, não a TV.
- Não há tema visual configurável para a TV.
