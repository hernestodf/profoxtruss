# Segurança

## Prepared Statements
- **Obrigatório**: toda query SQL deve usar prepared statements (`$pdo->prepare()` + `->execute()`).
- Nunca concatenar valores em strings SQL.
- Placeholders nomeados (`:nome`) ou posicionais (`?`).

## Autenticação
- Senhas hasheadas com `password_hash()` (bcrypt, cost 12).
- Verificação com `password_verify()`.
- Sessão configurada com `cookie_httponly=1` e `use_strict_mode=1`.
- `SESSION_LIFETIME` configurável (default 7200s = 2h).
- Logout destrói a sessão (`session_destroy()`).

## CSRF
- Token CSRF gerado em `csrfToken()` e armazenado em sessão.
- Validado via `csrfValidate()` em toda requisição POST.
- Implementado nos controllers via `BaseController::csrf()`.

## XSS
- `htmlspecialchars()` aplicado em `post()` e `get()` helpers com `ENT_QUOTES`.
- Blade/Twig não usados — a proteção é manual via helpers.

## Headers
- `session.cookie_httponly=1` — impede acesso JS ao cookie de sessão.
- `session.use_strict_mode=1` — rejeita session IDs não iniciados pelo servidor.
- Sem CSP configurado (Tailwind/Alpine/Three.js via CDN exigem 'unsafe-inline').

## RBAC
- Verificação no middleware `Rbac` ou inline via `BaseController::authorize()`.
- Não confiar apenas na ocultação de UI — toda rota sensível tem verificação server-side.
- Permissões cacheadas em sessão: `invalidate()` após alterações.

## Secrets
- Apenas em `.env`. Nunca commitar `.env` (não incluso em versionamento).
- `.env` atual: sem secrets complexos (apenas APP_ENV, DB_PATH, SESSION_LIFETIME).

## Boas Práticas Adicionais
- Validar/sanitizar toda entrada (CPF validado por algoritmo de dígitos verificadores).
- `ON DELETE RESTRICT` em relações fortes (pedidos → clientes, itens → produtos).
- Não expor mensagens de erro internas em produção (`APP_DEBUG=false`).
