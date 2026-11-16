-- Dados de demonstração (carregados só quando o banco está vazio). Datas relativas a hoje.
-- Logins: admin@sesi.local / admin123 · revisor@sesi.local / revisor123 · editor@sesi.local / editor123  (TROQUE AS SENHAS)

INSERT INTO conteudo_tv(id, nome, tempo_padrao) VALUES (1, 'TV da entrada', 14);

INSERT INTO usuario(id, nome, email, senha_hash, perfil, criado_em) VALUES
 (1, 'Administrador',  'admin@sesi.local',   '$2b$12$vcVIgL12bVP2F3/.knSHk.FnPQr4YNL1wujEn.9Ft6nSM0u7OgRYi', 'administrador', datetime('now','localtime')),
 (2, 'Revisora Marta', 'revisor@sesi.local', '$2b$12$v6LNGlLgD32S.wC1Buw6SuxHye7sE8LHEGZLx3mEm8oGYp9PI9vbC', 'revisor',       datetime('now','localtime')),
 (3, 'Editor Carlos',  'editor@sesi.local',  '$2b$12$6Sn972rLyPO4eaKS9O1.Q.l7W0Z/Sut50/aaOE16LlIn9c/SixBUu', 'editor',        datetime('now','localtime'));

INSERT INTO turma(id, nome) VALUES (1, '1º EM A'), (2, '1º EM B'), (3, '2º EM A'), (4, '3º EM A'), (5, '9º Ano A');

INSERT INTO evento(titulo, descricao, tipo, data_inicio, local, publico, criado_por) VALUES
 ('Feira de Ciências',      'Exposição dos projetos dos alunos.', 'evento',            date('now','localtime','+6 day'),  'Ginásio',      1, 3),
 ('Prova Mensal – 1º EM',   'Manhã e tarde.',                     'atividade',         date('now','localtime','+3 day'),  'Salas de aula', 1, 3),
 ('Reunião de pais',        'Entrega de resultados.',             'reuniao',           date('now','localtime','+10 day'), 'Auditório',     0, 3),
 ('Dia do Estudante',       'Programação especial.',              'data_comemorativa', date('now','localtime','+14 day'), 'Pátio',         1, 3);

INSERT INTO publicacao(id, tipo, titulo, descricao, disciplina, data_ref, prazo, autor_id, publico_alvo, visibilidade, exibir_tv, status, prioridade, duracao_seg, inicio_exibicao, fim_exibicao, criado_em, atualizado_em) VALUES
 (1, 'aviso',   'Bem-vindos ao Sesi', 'Aqui você encontra as principais informações da escola. Tenha uma ótima semana!', NULL, NULL, NULL, 3, 'geral', 'publico', 1, 'aprovado', 1, 12, datetime('now','localtime','-1 day'), datetime('now','localtime','+30 day'), datetime('now','localtime'), datetime('now','localtime')),
 (2, 'noticia', 'Sesi realiza Feira de Ciências 2026', 'Os alunos apresentam projetos de ciência e tecnologia para toda a comunidade.', NULL, NULL, NULL, 3, 'geral', 'publico', 1, 'aprovado', 1, 12, datetime('now','localtime','-1 day'), datetime('now','localtime','+20 day'), datetime('now','localtime'), datetime('now','localtime')),
 (3, 'tarefa',  'Lista de exercícios – Funções', 'Resolver os exercícios 1 a 10 do capítulo 4.', 'Matemática', NULL, date('now','localtime','+4 day'), 3, 'turmas', 'interno', 0, 'aprovado', 1, 12, datetime('now','localtime','-1 day'), datetime('now','localtime','+5 day'), datetime('now','localtime'), datetime('now','localtime')),
 (4, 'prova',   'Prova bimestral de Português', 'Conteúdo: interpretação de texto e gramática.', 'Português', date('now','localtime','+7 day'), NULL, 3, 'turmas', 'interno', 0, 'aprovado', 1, 12, datetime('now','localtime','-1 day'), datetime('now','localtime','+8 day'), datetime('now','localtime'), datetime('now','localtime')),
 (5, 'recado_individual', 'Recado individual (exemplo privado)', 'Aluno com 5 faltas no mês. Contato da família: (18) 99999-0000.', NULL, NULL, NULL, 3, 'turmas', 'privado', 0, 'aprovado', 1, 12, NULL, NULL, datetime('now','localtime'), datetime('now','localtime')),
 (6, 'aviso',   'Reunião pedagógica adiada', 'A reunião será remarcada. Novas informações em breve.', NULL, NULL, NULL, 3, 'geral', 'publico', 1, 'em_revisao', 1, 12, datetime('now','localtime'), datetime('now','localtime','+10 day'), datetime('now','localtime'), datetime('now','localtime')),
 (7, 'aviso',   'Campanha de vacinação (encerrada)', 'Campanha realizada na semana passada.', NULL, NULL, NULL, 3, 'geral', 'publico', 1, 'aprovado', 1, 12, datetime('now','localtime','-14 day'), datetime('now','localtime','-1 day'), datetime('now','localtime'), datetime('now','localtime'));

INSERT INTO publicacao_turma(publicacao_id, turma_id) VALUES (3, 1), (4, 1), (4, 2), (5, 1);

INSERT INTO item_exibicao(conteudo_tv_id, publicacao_id, prioridade, duracao_seg) VALUES (1, 1, 1, 12), (1, 2, 1, 12), (1, 6, 1, 12), (1, 7, 1, 12);

INSERT INTO revisao_publicacao(publicacao_id, usuario_id, acao, comentario, criado_em) VALUES
 (1, 2, 'aprovada', NULL, datetime('now','localtime')), (2, 2, 'aprovada', NULL, datetime('now','localtime')), (3, 2, 'aprovada', NULL, datetime('now','localtime')), (4, 2, 'aprovada', NULL, datetime('now','localtime')),
 (5, 2, 'aprovada', NULL, datetime('now','localtime')), (6, 3, 'enviada',  NULL, datetime('now','localtime')), (7, 2, 'aprovada', NULL, datetime('now','localtime'));

-- Cardápio da semana atual (segunda-feira)
INSERT INTO cardapio_semanal(id, semana_inicio, titulo, status, criado_por) VALUES (1, date('now','localtime','-6 days','weekday 1'), 'Cardápio da semana', 'publicado', 3);
INSERT INTO cardapio_dia(cardapio_id, dia_semana, refeicao, descricao) VALUES
 (1,1,'manha','Suco e biscoito'),            (1,1,'tarde','Arroz, feijão, frango grelhado, salada e fruta'), (1,1,'noite','Sopa de legumes e pão'),
 (1,2,'manha','Leite com pão'),              (1,2,'tarde','Arroz, feijão, carne moída, purê e salada'),      (1,2,'noite','Arroz, feijão e omelete'),
 (1,3,'manha','Suco e bolo'),                (1,3,'tarde','Arroz, feijão, peixe assado, legumes e fruta'),   (1,3,'noite','Macarrão ao molho'),
 (1,4,'manha','Leite com cereal'),           (1,4,'tarde','Arroz, feijão, macarrão, almôndegas e salada'),   (1,4,'noite','Caldo de feijão e torrada'),
 (1,5,'manha','Suco e biscoito'),            (1,5,'tarde','Arroz, feijão, frango ao molho, farofa e fruta'), (1,5,'noite','Pão com queijo e fruta');

-- Aniversariantes do mês atual
INSERT INTO aniversariante(nome, turma_id, data_nascimento, autorizado) VALUES
 ('Ana Clara Souza',     1, '2011-' || strftime('%m','now','localtime') || '-03', 1),
 ('Lucas Martins',       1, '2011-' || strftime('%m','now','localtime') || '-09', 1),
 ('Pedro Henrique',      2, '2011-' || strftime('%m','now','localtime') || '-12', 1),
 ('Maria Eduarda Lima',  2, '2011-' || strftime('%m','now','localtime') || '-18', 1),
 ('Beatriz Santos',      4, '2011-' || strftime('%m','now','localtime') || '-20', 1),
 ('Gabriel Almeida',     3, '2011-' || strftime('%m','now','localtime') || '-27', 1),
 ('Aluno sem autorização', 1, '2011-' || strftime('%m','now','localtime') || '-15', 0);
