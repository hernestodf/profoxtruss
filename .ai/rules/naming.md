# Regras: Naming

## PHP
- **Classes**: `PascalCase`. Ex: `HomeController`, `UserRepository`, `RbacService`, `Layout`.
- **Métodos**: `camelCase`. Ex: `listarPecas()`, `salvarProjeto()`, `grantPermission()`.
- **Funções globais**: `snake_case` ou `camelCase` (helpers.php). Ex: `url()`, `csrfToken()`, `isLoggedIn()`, `appLog()`.
- **Variáveis**: `$camelCase` ou `$snake_case`. Ex: `$cliente_id`, `$userRole`.
- **Constantes**: `UPPER_SNAKE_CASE`. Ex: `APP_ENV`, `BASE_PATH`, `ROOT`.

## Banco de Dados
- **Tabelas**: `snake_case` no plural. Ex: `users`, `clientes`, `pedido_itens`, `role_permissions`.
- **Colunas**: `snake_case`. Ex: `cliente_id`, `preco_unit`, `created_at`.
- **Índices**: `idx_{tabela}_{coluna}`. Ex: `idx_users_email`, `idx_clientes_cpf`.

## JavaScript
- **Classes/Components**: `PascalCase` com prefixo `Ui`. Ex: `UiModal`, `UiToastContainer`, `UiFormInput`.
- **Funções**: `camelCase`. Ex: `getTrussSegmentsForDistance()`, `magneticSnap()`.
- **Arquivos JS**: `PascalCase.js` (mesmo nome da classe). Ex: `UiModal.js`, `UiPopover.js`.

## CSS
- **Arquivos**: `snake_case` com prefixo `_` para partials. Ex: `_variables.css`, `_buttons.css`, `_sidebar.css`.
- **Classes**: Atômicas, curtas, `kebab-case`. Ex: `.btn-primary`, `.modal-overlay`, `.cal-cell`, `.tog-track`.
- **Custom properties**: `--kebab-case`. Ex: `--color-neon-cyan`, `--shadow-neon-sm`.

## Rotas
- **URLs**: `snake_case` ou `kebab-case`. Ex: `/admin/users`, `/pecas`, `/api/projects`.
- **Arquivos**: `snake_case.php`. Ex: `routes/clientes.php`, `routes/api.php`.

## Views
- **Subdiretórios**: `snake_case` (mesmo nome do módulo). Ex: `admin/`, `clientes/`, `pedidos/`.
- **Arquivos**: `snake_case.php`. Ex: `form.php`, `users.php`, `login.php`.
