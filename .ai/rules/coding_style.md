# Regras: Coding Style

## PHP
- **PSR-1/PSR-2-like**: Classes em `PascalCase`, métodos em `camelCase`, constantes em `UPPER_SNAKE_CASE`.
- **Tipagem**: PHP 8.4 com tipos declarados em parâmetros e retornos.
- **Controllers**: Estendem `BaseController`, usam `view()`/`json()` para resposta.
- **Repositories**: Recebem `?PDO $pdo = null` no construtor (default `getPDO()`), sem exceções — retornam arrays ou false.
- **Services**: Classes estáticas ou instância única (RbacService é estático).
- **Middlewares**: Classe estática com método `handle()` (com parâmetros opcionais).
- **Views**: Usam `Layout::start('content')` / `Layout::end()`, HTML misturado com PHP minimalista.
- **Helpers**: Funções globais em `app/helpers.php` sem namespace.
- **Autoload**: PSR-4-like registrado em `index.php` (não usa Composer).
- **Exclusão de registros em HTML**: Usar `Router::post('/{id}/deletar', ...)` com formulário POST (não `DELETE` HTTP). Para API JSON, usar `Router::delete()`. Não usar `_method` override.

## JavaScript
- ES6 modules em `public/assets/js/components/*.js`.
- Web Components com `customElements.define()`.
- Alpine.js para interatividade em views (atributos `x-data`, `x-show`, `@click`).
- Fabric.js manipulado via funções globais expostas no `window` para compatibilidade com Alpine.

## CSS
- Arquivo principal `styles.css` com `@import` para partials em `base/`, `components/`, `layout/`.
- Nomes de classes atômicas: `.btn`, `.btn-{color}`, `.badge`, `.tog`, `.fi`, `.fl`, `.fg`.
- CSS custom properties para o tema neon em `_variables.css`.
- Tailwind CSS via CDN para layout geral (grid, flex, spacing).

## SQL
- Queries diretas nos repositories (sem ORM ou query builder).
- Prepared statements sempre (nunca concatenar).
- Placeholders nomeados (`:nome`) ou posicionais (`?`).
