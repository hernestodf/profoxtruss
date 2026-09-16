# Regras: API

## Formato de Resposta
- Toda resposta JSON deve conter `ok` (bool — não `success`, corrigido 2026-08-18)
  e o payload em `data` ou `error`.
- Status HTTP 200 para sucesso, 400/403/404 para erros conhecidos, 500 para erros internos.

## Validação
- Validar entrada no controller antes de passar ao repository.
- Usar `BaseController::validate()` para regras simples.
- Retornar `json(['ok' => false, 'error' => 'mensagem'], 400)` em validação.

## Persistência
- Projetos: campo `components` deve ser JSON string (via `json_encode`/`json_decode` no controller).
- Peças: recalcular `estoque` no controller antes de salvar.
- Pedidos: subtotal recalculado server-side (nunca confiar no cliente).

## Autenticação
- Toda rota `/api/*` deve usar middleware `Auth`.
- Não usar tokens — a sessão HTTP é o mecanismo de autenticação.
- Rotas que exigem permissão adicional devem usar `Rbac:action` no grupo ou `authorize()` no controller.

## Convenções
- Sempre usar `BaseController::json()` para resposta, nunca `echo` direto.
- Endpoints novos devem seguir o padrão `/api/{recurso}`.
- Use `Router::delete()` para exclusão em API JSON, nunca POST com `_method`.
- Parâmetros de busca devem ser query string (`?q=termo`).
