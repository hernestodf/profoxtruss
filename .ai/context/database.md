# Banco de Dados — SQLite

## Tecnologia
- SQLite via PDO (`pdo_sqlite`)
- Driver único: não há suporte a MySQL/PostgreSQL
- Arquivo de banco em `DB_PATH` (configurado em `.env`, relativo a `config.php`)
- Singleton `getPDO()` — uma conexão por request

## Schema (8 tabelas)

### `users`
| Coluna | Tipo | Notas |
|--------|------|-------|
| id | INTEGER PK | AUTOINCREMENT |
| name | TEXT | NOT NULL |
| email | TEXT | UNIQUE, NOT NULL |
| password | TEXT | hash bcrypt, NOT NULL |
| role | TEXT | DEFAULT 'user' |
| created_at | TEXT | DEFAULT CURRENT_TIMESTAMP |
| updated_at | TEXT | DEFAULT CURRENT_TIMESTAMP |
- Índices: `idx_users_email`, `idx_users_role`

### `roles`
| Coluna | Tipo | Notas |
|--------|------|-------|
| id | INTEGER PK | AUTOINCREMENT |
| name | TEXT | UNIQUE, NOT NULL |
| label | TEXT | NOT NULL |
| created_at | TEXT | DEFAULT CURRENT_TIMESTAMP |

### `permissions`
| Coluna | Tipo | Notas |
|--------|------|-------|
| id | INTEGER PK | AUTOINCREMENT |
| action | TEXT | UNIQUE, NOT NULL (ex: 'user.edit') |
| label | TEXT | DEFAULT '' |
| created_at | TEXT | DEFAULT CURRENT_TIMESTAMP |

### `role_permissions`
| Coluna | Tipo | Notas |
|--------|------|-------|
| role_id | INTEGER | FK → roles(id) ON DELETE CASCADE |
| permission_id | INTEGER | FK → permissions(id) ON DELETE CASCADE |
- PK composta (role_id, permission_id)

### `clientes`
| Coluna | Tipo | Notas |
|--------|------|-------|
| id | INTEGER PK | AUTOINCREMENT |
| nome | TEXT | NOT NULL |
| cpf | TEXT | UNIQUE, NOT NULL (armazenado formatado) |
| created_at | TEXT | DEFAULT CURRENT_TIMESTAMP |
| updated_at | TEXT | DEFAULT CURRENT_TIMESTAMP |
- Índices: `idx_clientes_cpf`, `idx_clientes_nome`

### `produtos`
| Coluna | Tipo | Notas |
|--------|------|-------|
| id | INTEGER PK | AUTOINCREMENT |
| codigo | TEXT | UNIQUE, NOT NULL |
| nome | TEXT | NOT NULL |
| tipo | TEXT | NOT NULL ('Q30', 'sleeve', 'sapata') |
| comprimento | REAL | em metros |
| peso | REAL | em kg |
| preco | REAL | |
| estoque | INTEGER | |
| ativo | INTEGER | 1 ou 0 |
| cor | TEXT | hex, DEFAULT '' — cor real de render (2D/3D), usada por `montante`/`travessa` (adicionada 2026-08-17 via `ALTER TABLE`) |
| created_at | TEXT | DEFAULT CURRENT_TIMESTAMP |
- Índices: `idx_produtos_nome`, `idx_produtos_codigo`, `idx_produtos_tipo`

### `pedidos`
| Coluna | Tipo | Notas |
|--------|------|-------|
| id | INTEGER PK | AUTOINCREMENT |
| cliente_id | INTEGER | FK → clientes(id) ON DELETE RESTRICT |
| status | TEXT | DEFAULT 'rascunho' |
| desconto | REAL | percentual |
| total | REAL | |
| observacao | TEXT | nullable |
| created_at | TEXT | DEFAULT CURRENT_TIMESTAMP |
| updated_at | TEXT | DEFAULT CURRENT_TIMESTAMP |
- Índices: `idx_pedidos_cliente`, `idx_pedidos_status`

### `pedido_itens`
| Coluna | Tipo | Notas |
|--------|------|-------|
| id | INTEGER PK | AUTOINCREMENT |
| pedido_id | INTEGER | FK → pedidos(id) ON DELETE CASCADE |
| produto_id | INTEGER | FK → produtos(id) ON DELETE RESTRICT |
| quantidade | INTEGER | DEFAULT 1 |
| preco_unit | REAL | preço congelado no momento da venda |
| subtotal | REAL | recalculado server-side |
- Índice: `idx_pedido_itens_pedido`

### `projects`
| Coluna | Tipo | Notas |
|--------|------|-------|
| id | INTEGER PK | AUTOINCREMENT |
| name | TEXT | NOT NULL |
| width | REAL | em metros |
| height | REAL | em metros |
| length | REAL | em metros |
| scale | INTEGER | DEFAULT 50 (1m = 50px) |
| components | TEXT | JSON string |
| created_at | TEXT | DEFAULT CURRENT_TIMESTAMP |

## Migrations
- `database/migration.sql` — CREATE TABLE completo (113 linhas)
- `database/seed.sql` — dados iniciais: roles, permissions, usuário admin, peças exemplo, clientes exemplo (109 linhas)
- Admin inicial: `admin@exemplo.com` / `admin123`

## Observações
- Chaves estrangeiras com `ON DELETE CASCADE` (role_permissions, pedido_itens) e `ON DELETE RESTRICT` (pedidos → clientes, pedido_itens → produtos)
- `produtos.tipo` não usa ENUM — é TEXT livre, mas na prática usa 'Q30', 'sleeve', 'sapata', 'plana', 'braco', 'cubo', 'grepo', 'montante', 'travessa'
- Coluna `components` em `projects` armazena JSON serializado com lista de componentes do projeto
