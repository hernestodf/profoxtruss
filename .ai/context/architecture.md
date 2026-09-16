# Arquitetura

## Camadas e Fluxo

### 1. Entry Point (`public/index.php`)
- Define `ROOT` como `dirname(__DIR__)`
- Carrega `config.php` (env, sessão, PDO, constantes)
- Carrega `app/helpers.php` (funções globais)
- Registra autoloader PSR-4-like para namespaces em `app/`
- Registra `ErrorHandler` (converte erros em exceções)
- Calcula `BASE_PATH` dinamicamente de `$_SERVER['SCRIPT_NAME']`
- Requer `routes.php` para registrar todas as rotas
- Resolve `REQUEST_URI` contra `BASE_PATH` (com fallbacks para subdiretório, htaccess, index.php)
- Chama `Router::match()` -> middleware pipeline -> controller

### 2. Config (`config.php`)
- `Env::load()` carrega `.env`
- Sessão configurada com `cookie_httponly`, `use_strict_mode`, `gc_maxlifetime`
- Define constantes: `APP_ENV`, `APP_DEBUG`, `LOG_TRACE`
- `getPDO()` — lazy singleton SQLite PDO

### 3. Roteamento (`Router`)
Classe estática em `app/utils/Router.php`:
- Registro: `get()`, `post()`, `put()`, `delete()`, `any()`
- `group($prefix, $middlewares, $callback)` — prefixo + middlewares aninháveis
- `match($method, $uri)` — retorna `[class, method, middlewares, params]` ou null
- Placeholders `{param}` convertidos para regex `([^/]+)`
- Rotas registradas em `routes.php` e `routes/*.php`

### 4. Middleware Pipeline
Executado em sequência antes do controller:
1. `Auth::handle()` — verifica `$_SESSION['user_id']`, redireciona para `/login` se ausente
2. `Rbac::handle($action)` — delega para `RbacService::authorize($action)`, 403 se sem permissão

### 5. Controller Layer
Controllers em `app/controllers/` estendem `BaseController`:
- `view($template, $data)` — renderiza via `Layout::render()`
- `json($data, $status)` — resposta JSON
- `redirect($url)` — redireciona
- `authorize($action)` — verificação RBAC inline
- `csrf()` — valida CSRF
- `param($key, $default)` — lê parâmetros de rota
- `validate($rules, $data)` — validação simples
- `notFound()`, `forbidden()` — respostas de erro

### 6. Repository Layer
Repositories em `app/repositories/` recebem PDO opcional no construtor (default `getPDO()`):
- `UserRepository`, `ClienteRepository`, `PedidoRepository`, `ProdutoRepository`, `ProjectRepository`, `PermissionRepository`
- Sem models — repositories fazem queries SQL diretamente

### 7. View Layer
Template engine `Layout` (`app/utils/Layout.php`):
- `set($name)` — define layout
- `block($name, $content)` / `start($name)` / `end()` — define blocos
- `slot($name)` — renderiza bloco no layout
- `render($view, $data)` — carrega view, depois layout
- Views em `app/views/` organizadas por domínio

### 8. Design System
- 19+ Web Components customizados (`customElements.define`) em `public/assets/js/components/*.js`
- CSS modular em `public/assets/css/` com `@import`
- Único layout: `app/views/layouts/main.php` (sidebar + navbar + drawer)

## Diagrama de Dependências
```
index.php
 ├── config.php (PDO singleton)
 ├── helpers.php (funções globais)
 ├── Router.php (registro/match de rotas)
 ├── middlewares/ (Auth, Rbac)
 ├── controllers/ (herdam BaseController)
 │    └── repositories/ (PDO via construtor)
 │         └── services/ (RbacService)
 └── Layout.php (template engine)
      └── views/ (layouts, páginas, componentes)
```

## Limitações Arquiteturais
- PDO singleton (`static $pdo`): impossível múltiplas conexões simultâneas
- Router não suporta PUT/DELETE nativo em formulários HTML (só GET/POST; DELETE existe apenas via `Router::delete()` para JSON)
- Não há suporte a cache distribuído, filas, ou eventos assíncronos
