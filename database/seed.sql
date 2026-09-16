-- ============================================================================
-- SEED — Dados iniciais para SQLite
-- Rodar após migration.sql
-- ============================================================================

-- ── Cargos ──────────────────────────────────────────────────────────────────
INSERT OR IGNORE INTO roles (name, label) VALUES
    ('admin',  'Administrador'),
    ('editor', 'Editor'),
    ('user',   'Usuário');

-- ── Permissões disponíveis ───────────────────────────────────────────────────
INSERT OR IGNORE INTO permissions (action, label) VALUES
    ('dashboard.view', 'Ver dashboard'),
    ('admin.view',     'Acessar painel admin'),
    ('user.view',      'Listar usuários'),
    ('user.edit',      'Editar usuários'),
    ('user.delete',    'Excluir usuários'),
    ('report.view',    'Ver relatórios'),
    ('report.export',  'Exportar relatórios');

-- ── Permissões por cargo ─────────────────────────────────────────────────────
-- admin: tudo
INSERT OR IGNORE INTO role_permissions (role_id, permission_id)
    SELECT r.id, p.id FROM roles r, permissions p
    WHERE r.name = 'admin';

-- editor: dashboard + relatórios
INSERT OR IGNORE INTO role_permissions (role_id, permission_id)
    SELECT r.id, p.id FROM roles r, permissions p
    WHERE r.name = 'editor'
    AND   p.action IN ('dashboard.view', 'report.view');

-- user: só dashboard
INSERT OR IGNORE INTO role_permissions (role_id, permission_id)
    SELECT r.id, p.id FROM roles r, permissions p
    WHERE r.name = 'user'
    AND   p.action = 'dashboard.view';

-- ── Usuário admin inicial ────────────────────────────────────────────────────
-- Senha: admin123  (trocar imediatamente após o primeiro login)
INSERT OR IGNORE INTO users (name, email, password, role) VALUES (
    'Administrador',
    'admin@exemplo.com',
    '$2y$12$uVvYdEaz5hmpmfOT6pOpxO61SOfdLwskcHPU8RUlw/WCRziPIbLEi', -- admin123
    'admin'
);

-- ── Permissões do módulo Clientes ────────────────────────────────────────────
INSERT OR IGNORE INTO permissions (action, label) VALUES
    ('cliente.view',   'Listar clientes'),
    ('cliente.create', 'Criar cliente'),
    ('cliente.edit',   'Editar cliente'),
    ('cliente.delete', 'Excluir cliente');

-- admin: todas as permissões de cliente
INSERT OR IGNORE INTO role_permissions (role_id, permission_id)
    SELECT r.id, p.id FROM roles r, permissions p
    WHERE r.name = 'admin'
    AND   p.action LIKE 'cliente.%';

-- editor: só ver e criar
INSERT OR IGNORE INTO role_permissions (role_id, permission_id)
    SELECT r.id, p.id FROM roles r, permissions p
    WHERE r.name = 'editor'
    AND   p.action IN ('cliente.view', 'cliente.create');

-- Clientes de exemplo
INSERT OR IGNORE INTO clientes (nome, cpf) VALUES
    ('João da Silva',   '111.444.777-35'),
    ('Maria Oliveira',  '222.555.888-46'),
    ('Carlos Pereira',  '333.666.999-57');

-- ── Peças Box Truss Q30 de exemplo (Cadastrados na tabela produtos) ───────────
INSERT OR IGNORE INTO produtos (codigo, nome, tipo, comprimento, peso, preco, estoque, ativo) VALUES
    ('Q30-030', 'Box Truss Q30 0.30m', 'Q30', 0.30, 0.50, 50.00, 15, 1),
    ('Q30-050', 'Box Truss Q30 0.50m', 'Q30', 0.50, 0.80, 75.00, 20, 1),
    ('Q30-100', 'Box Truss Q30 1.00m', 'Q30', 1.00, 1.60, 120.00, 30, 1),
    ('Q30-150', 'Box Truss Q30 1.50m', 'Q30', 1.50, 2.40, 170.00, 25, 1),
    ('Q30-200', 'Box Truss Q30 2.00m', 'Q30', 2.00, 3.20, 220.00, 40, 1),
    ('Q30-300', 'Box Truss Q30 3.00m', 'Q30', 3.00, 4.80, 310.00, 50, 1),
    ('Q30-400', 'Box Truss Q30 4.00m', 'Q30', 4.00, 6.40, 400.00, 10, 1),
    ('Q30-500', 'Box Truss Q30 5.00m', 'Q30', 5.00, 8.00, 490.00, 8, 1),
    ('SLEEVE',  'Sleeve Conector Q30',  'sleeve', 0.00, 0.20, 30.00, 80, 1);

-- ── Treliça Plana (Flat Truss): 2 faces paralelas ligadas por diagonais ──────
-- Seção: 0.30m de altura x ~0.05m de espessura (não forma volume fechado).
INSERT OR IGNORE INTO produtos (codigo, nome, tipo, comprimento, peso, preco, estoque, ativo) VALUES
    ('PLANA-200', 'Treliça Plana Q30 2.00m', 'plana', 2.00, 1.80, 140.00, 5, 1),
    ('PLANA-250', 'Treliça Plana Q30 2.50m', 'plana', 2.50, 2.20, 165.00, 5, 1),
    ('PLANA-300', 'Treliça Plana Q30 3.00m', 'plana', 3.00, 2.70, 190.00, 5, 1);

-- ── Braço: 1 barra estrutural (elemento linear, sem faces — NÃO é treliça) ───
-- Hierarquia: Box Truss (4 faces) > Treliça Plana (2 faces) > Braço (1 barra).
INSERT OR IGNORE INTO produtos (codigo, nome, tipo, comprimento, peso, preco, estoque, ativo) VALUES
    ('BRACO-300', 'Braço 3.00m (barra)', 'braco', 3.00, 1.50, 120.00, 5, 1);

-- ── Sapata: base de apoio retangular (4 faces, sólida) ────────────────────────
-- Altura fixa: 3,5cm (0,035m). Largura fixa: 30cm (0,30m).
-- Comprimento varia por modelo. Sempre apoiada no solo (Y=0).
-- Faces: 4 estruturais. Função: apoio/base para torres Box Truss.
INSERT OR IGNORE INTO produtos (codigo, nome, tipo, comprimento, peso, preco, estoque, ativo) VALUES
    ('SAPATA-30X50', 'Sapata 0.30x0.50m', 'sapata', 0.50, 8.00, 250.00, 10, 1),
    ('SAPATA-30X60', 'Sapata 0.30x0.60m', 'sapata', 0.60, 9.50, 290.00, 8, 1),
    ('SAPATA-30X80', 'Sapata 0.30x0.80m', 'sapata', 0.80, 12.00, 360.00, 6, 1);

-- ── Perfil PM5: montante (só vertical) e travessa (só horizontal) ───────────
-- Perfil de alumínio extrudado 50x50mm (cruciforme, 4 câmaras + núcleo
-- central), diferente do Box Truss Q30. Cor identifica o modelo/comprimento
-- no catálogo e é usada como cor real de render (2D/3D).
-- Montante: NUNCA deitado (sempre ao longo de Y). Travessa: NUNCA em pé
-- (sempre no plano horizontal).
INSERT OR IGNORE INTO produtos (codigo, nome, tipo, comprimento, peso, preco, estoque, ativo, cor) VALUES
    ('TRAV-045', 'Travessa 0.45m', 'travessa', 0.45, 0.00, 0.00, 700, 1, '#3b82f6'),
    ('TRAV-095', 'Travessa 0.95m', 'travessa', 0.95, 0.00, 0.00, 700, 1, '#22c55e'),
    ('TRAV-195', 'Travessa 1.95m', 'travessa', 1.95, 0.00, 0.00, 400, 1, '#ef4444'),
    ('MONT-100', 'Montante 1.00m', 'montante', 1.00, 0.00, 0.00, 400, 1, '#f97316'),
    ('MONT-220', 'Montante 2.20m', 'montante', 2.20, 0.00, 0.00, 180, 1, '#92400e'),
    ('MONT-250', 'Montante 2.50m', 'montante', 2.50, 0.00, 0.00, 270, 1, '#a855f7'),
    ('MONT-300', 'Montante 3.00m', 'montante', 3.00, 0.00, 0.00, 80,  1, '#9ca3af');

-- ── Permissões do módulo Pedidos ─────────────────────────────────────────────
INSERT OR IGNORE INTO permissions (action, label) VALUES
    ('pedido.view',   'Listar pedidos'),
    ('pedido.create', 'Criar pedido'),
    ('pedido.edit',   'Editar pedido'),
    ('pedido.delete', 'Excluir pedido');

INSERT OR IGNORE INTO role_permissions (role_id, permission_id)
    SELECT r.id, p.id FROM roles r, permissions p
    WHERE r.name = 'admin' AND p.action LIKE 'pedido.%';

-- ── Permissões adicionais para o Módulo Projetos ─────────────────────────────
INSERT OR IGNORE INTO permissions (action, label) VALUES
    ('project.view',   'Visualizar projetos'),
    ('project.create', 'Criar projetos'),
    ('project.edit',   'Editar projetos'),
    ('project.delete', 'Excluir projetos');

INSERT OR IGNORE INTO role_permissions (role_id, permission_id)
    SELECT r.id, p.id FROM roles r, permissions p
    WHERE r.name = 'admin' AND p.action LIKE 'project.%';
