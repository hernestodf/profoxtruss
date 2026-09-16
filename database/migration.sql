-- ============================================================================
-- MIGRATION — Estrutura completa do banco SQLite
-- ============================================================================

-- Tabela de usuários
CREATE TABLE IF NOT EXISTS users (
    id         INTEGER PRIMARY KEY AUTOINCREMENT,
    name       TEXT NOT NULL,
    email      TEXT NOT NULL UNIQUE,
    password   TEXT NOT NULL,
    role       TEXT NOT NULL DEFAULT 'user',
    created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX IF NOT EXISTS idx_users_email ON users (email);
CREATE INDEX IF NOT EXISTS idx_users_role ON users (role);

-- Cargos disponíveis no sistema
CREATE TABLE IF NOT EXISTS roles (
    id         INTEGER PRIMARY KEY AUTOINCREMENT,
    name       TEXT NOT NULL UNIQUE,
    label      TEXT NOT NULL,
    created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
);

-- Catálogo de permissões (ações do sistema)
CREATE TABLE IF NOT EXISTS permissions (
    id         INTEGER PRIMARY KEY AUTOINCREMENT,
    action     TEXT NOT NULL UNIQUE,  -- ex: 'user.edit', 'report.export'
    label      TEXT NOT NULL DEFAULT '',
    created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
);

-- Relação N:N cargos <-> permissões
CREATE TABLE IF NOT EXISTS role_permissions (
    role_id       INTEGER NOT NULL,
    permission_id INTEGER NOT NULL,
    PRIMARY KEY (role_id, permission_id),
    FOREIGN KEY (role_id)       REFERENCES roles(id)       ON DELETE CASCADE,
    FOREIGN KEY (permission_id) REFERENCES permissions(id) ON DELETE CASCADE
);

-- ── Módulo: Clientes ─────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS clientes (
    id         INTEGER PRIMARY KEY AUTOINCREMENT,
    nome       TEXT NOT NULL,
    cpf        TEXT NOT NULL UNIQUE,   -- armazenado como 000.000.000-00
    created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX IF NOT EXISTS idx_clientes_cpf ON clientes (cpf);
CREATE INDEX IF NOT EXISTS idx_clientes_nome ON clientes (nome);

-- ── Módulo: Produtos (Catálogo de Peças Box Truss Q30 e outros) ─────────────────
CREATE TABLE IF NOT EXISTS produtos (
    id          INTEGER PRIMARY KEY AUTOINCREMENT,
    codigo      TEXT NOT NULL UNIQUE,
    nome        TEXT NOT NULL,
    tipo        TEXT NOT NULL, -- 'Q30', 'sleeve', 'sapata', 'montante', 'travessa', etc.
    comprimento REAL NOT NULL DEFAULT 0.00, -- em metros (ex: 1.50)
    peso        REAL NOT NULL DEFAULT 0.00, -- em kg (ex: 3.50)
    preco       REAL NOT NULL DEFAULT 0.00,
    estoque     INTEGER NOT NULL DEFAULT 0,
    ativo       INTEGER NOT NULL DEFAULT 1, -- 1=sim, 0=não
    cor         TEXT NOT NULL DEFAULT '', -- cor de identificação/render (hex), usada por montante/travessa
    created_at  TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX IF NOT EXISTS idx_produtos_nome ON produtos (nome);
CREATE INDEX IF NOT EXISTS idx_produtos_codigo ON produtos (codigo);
CREATE INDEX IF NOT EXISTS idx_produtos_tipo ON produtos (tipo);

-- ── Módulo: Pedidos ──────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS pedidos (
    id           INTEGER PRIMARY KEY AUTOINCREMENT,
    cliente_id   INTEGER NOT NULL,
    status       TEXT NOT NULL DEFAULT 'rascunho', -- 'rascunho','confirmado','cancelado'
    desconto     REAL NOT NULL DEFAULT 0.00,  -- percentual
    total        REAL NOT NULL DEFAULT 0.00,
    observacao   TEXT,
    created_at   TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at   TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (cliente_id) REFERENCES clientes(id) ON DELETE RESTRICT
);

CREATE INDEX IF NOT EXISTS idx_pedidos_cliente ON pedidos (cliente_id);
CREATE INDEX IF NOT EXISTS idx_pedidos_status ON pedidos (status);

CREATE TABLE IF NOT EXISTS pedido_itens (
    id          INTEGER PRIMARY KEY AUTOINCREMENT,
    pedido_id   INTEGER NOT NULL,
    produto_id  INTEGER NOT NULL,
    quantidade  INTEGER NOT NULL DEFAULT 1,
    preco_unit  REAL NOT NULL,   -- preço no momento da venda
    subtotal    REAL NOT NULL,
    FOREIGN KEY (pedido_id)  REFERENCES pedidos(id)  ON DELETE CASCADE,
    FOREIGN KEY (produto_id) REFERENCES produtos(id) ON DELETE RESTRICT
);

CREATE INDEX IF NOT EXISTS idx_pedido_itens_pedido ON pedido_itens (pedido_id);

-- ── Módulo: Projetos de Box Truss ───────────────────────────────────────────
CREATE TABLE IF NOT EXISTS projects (
    id          INTEGER PRIMARY KEY AUTOINCREMENT,
    name        TEXT NOT NULL,
    width       REAL NOT NULL DEFAULT 0.00,
    height      REAL NOT NULL DEFAULT 0.00,
    length      REAL NOT NULL DEFAULT 0.00,
    scale       INTEGER NOT NULL DEFAULT 50, -- 1m = 50px
    components  TEXT NOT NULL, -- JSON string contendo os componentes salvos
    created_at  TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
);
