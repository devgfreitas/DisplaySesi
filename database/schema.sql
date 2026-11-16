-- Central de conteúdo Sesi · esquema (SQLite)
-- Segue o DER inicial: USUARIO 1-N PUBLICACAO, PUBLICACAO N-N TURMA, PUBLICACAO 1-N REVISAO_PUBLICACAO,
-- CARDAPIO_SEMANAL 1-N CARDAPIO_DIA, EVENTO 1-N PUBLICACAO (opcional), CONTEUDO_TV 1-N ITEM_EXIBICAO.

CREATE TABLE IF NOT EXISTS usuario (
    id INTEGER PRIMARY KEY,
    nome TEXT NOT NULL,
    email TEXT NOT NULL UNIQUE,
    senha_hash TEXT NOT NULL,
    perfil TEXT NOT NULL CHECK (perfil IN ('editor','revisor','administrador')),
    ativo INTEGER NOT NULL DEFAULT 1,
    criado_em TEXT
);

CREATE TABLE IF NOT EXISTS turma (
    id INTEGER PRIMARY KEY,
    nome TEXT NOT NULL UNIQUE,
    ativo INTEGER NOT NULL DEFAULT 1
);

CREATE TABLE IF NOT EXISTS evento (
    id INTEGER PRIMARY KEY,
    titulo TEXT NOT NULL,
    descricao TEXT,
    tipo TEXT NOT NULL DEFAULT 'evento',            -- evento, data_comemorativa, reuniao, atividade
    data_inicio TEXT NOT NULL,
    data_fim TEXT,
    local TEXT,
    imagem TEXT,
    publico INTEGER NOT NULL DEFAULT 1,             -- 1 = pode aparecer no calendário da TV
    criado_por INTEGER REFERENCES usuario(id)
);

CREATE TABLE IF NOT EXISTS publicacao (
    id INTEGER PRIMARY KEY,
    tipo TEXT NOT NULL,                             -- comunicado, aviso, noticia, recado, prova, tarefa, recado_individual
    titulo TEXT NOT NULL,
    descricao TEXT,
    disciplina TEXT,
    data_ref TEXT,                                  -- data da prova / do recado
    prazo TEXT,                                     -- prazo da tarefa
    imagem TEXT,
    autor_id INTEGER NOT NULL REFERENCES usuario(id),
    evento_id INTEGER REFERENCES evento(id),
    publico_alvo TEXT NOT NULL DEFAULT 'geral',     -- geral | turmas
    visibilidade TEXT NOT NULL DEFAULT 'interno',   -- publico | interno | privado
    exibir_tv INTEGER NOT NULL DEFAULT 0,
    status TEXT NOT NULL DEFAULT 'rascunho',        -- rascunho, em_revisao, aprovado, rejeitado, arquivado
    prioridade INTEGER NOT NULL DEFAULT 1,          -- 1 normal, 2 alta, 3 urgente
    duracao_seg INTEGER NOT NULL DEFAULT 12,
    inicio_exibicao TEXT,
    fim_exibicao TEXT,
    criado_em TEXT,
    atualizado_em TEXT
);

CREATE TABLE IF NOT EXISTS publicacao_turma (
    publicacao_id INTEGER NOT NULL REFERENCES publicacao(id) ON DELETE CASCADE,
    turma_id INTEGER NOT NULL REFERENCES turma(id),
    PRIMARY KEY (publicacao_id, turma_id)
);

CREATE TABLE IF NOT EXISTS revisao_publicacao (
    id INTEGER PRIMARY KEY,
    publicacao_id INTEGER NOT NULL REFERENCES publicacao(id) ON DELETE CASCADE,
    usuario_id INTEGER NOT NULL REFERENCES usuario(id),
    acao TEXT NOT NULL,                             -- criada, editada, enviada, aprovada, rejeitada, arquivada
    comentario TEXT,
    criado_em TEXT
);

CREATE TABLE IF NOT EXISTS cardapio_semanal (
    id INTEGER PRIMARY KEY,
    semana_inicio TEXT NOT NULL UNIQUE,             -- sempre a segunda-feira
    titulo TEXT,
    imagem TEXT,
    status TEXT NOT NULL DEFAULT 'rascunho',        -- rascunho | publicado
    criado_por INTEGER REFERENCES usuario(id)
);

CREATE TABLE IF NOT EXISTS cardapio_dia (
    id INTEGER PRIMARY KEY,
    cardapio_id INTEGER NOT NULL REFERENCES cardapio_semanal(id) ON DELETE CASCADE,
    dia_semana INTEGER NOT NULL,                    -- 1 (segunda) a 5 (sexta)
    refeicao TEXT NOT NULL,                         -- manha | tarde | noite
    descricao TEXT,
    UNIQUE (cardapio_id, dia_semana, refeicao)
);

CREATE TABLE IF NOT EXISTS aniversariante (
    id INTEGER PRIMARY KEY,
    nome TEXT NOT NULL,
    turma_id INTEGER REFERENCES turma(id),
    data_nascimento TEXT NOT NULL,
    foto TEXT,
    autorizado INTEGER NOT NULL DEFAULT 0,          -- só aparece na TV com autorização
    obs_autorizacao TEXT
);

CREATE TABLE IF NOT EXISTS conteudo_tv (
    id INTEGER PRIMARY KEY,
    nome TEXT NOT NULL,
    tempo_padrao INTEGER NOT NULL DEFAULT 14
);

CREATE TABLE IF NOT EXISTS item_exibicao (
    id INTEGER PRIMARY KEY,
    conteudo_tv_id INTEGER NOT NULL REFERENCES conteudo_tv(id),
    publicacao_id INTEGER NOT NULL UNIQUE REFERENCES publicacao(id) ON DELETE CASCADE,
    prioridade INTEGER NOT NULL DEFAULT 1,
    duracao_seg INTEGER NOT NULL DEFAULT 12,
    ativo INTEGER NOT NULL DEFAULT 1
);

CREATE TABLE IF NOT EXISTS visualizacao (
    id INTEGER PRIMARY KEY,
    publicacao_id INTEGER,
    origem TEXT,
    criado_em TEXT
);

CREATE INDEX IF NOT EXISTS idx_publicacao_status ON publicacao(status, tipo);
CREATE INDEX IF NOT EXISTS idx_publicacao_autor ON publicacao(autor_id);
CREATE INDEX IF NOT EXISTS idx_aniversariante_data ON aniversariante(data_nascimento);
