# Padrões de API

## Endpoints
Rotas de API são registradas em `routes/api.php` e prefixadas com `/api`. Middleware `Auth` obrigatório.

### Peças (Produtos)
| Método | Rota | Ação | Descrição |
|--------|------|------|-----------|
| GET | `/api/pecas` | `ApiController::listarPecas` | Lista peças com filtro opcional `?tipo=` |
| POST | `/api/pecas` | `ApiController::salvarPeca` | Cria/atualiza peça |
| DELETE | `/api/pecas/{id}` | `ApiController::deletarPeca` | Remove peça |

### Projetos
| Método | Rota | Ação | Descrição |
|--------|------|------|-----------|
| GET | `/api/projects` | `ApiController::listarProjetos` | Lista projetos |
| GET | `/api/projects/{id}` | `ApiController::getProjeto` | Detalhe do projeto |
| POST | `/api/projects` | `ApiController::salvarProjeto` | Cria/atualiza projeto |
| DELETE | `/api/projects/{id}` | `ApiController::deletarProjeto` | Remove projeto |

### Endpoints Inativos (em `routes/pedidos.php`, desativados)
| Método | Rota | Ação | Descrição |
|--------|------|------|-----------|
| GET | `/api/clientes/busca` | `ApiController::buscarClientes` | Busca clientes por nome/CPF |
| GET | `/api/produtos/busca` | `ApiController::buscarProdutos` | Busca produtos por nome/código |
| GET | `/api/produtos/{id}` | `ApiController::getProduto` | Detalhe do produto |

## Formato de Resposta
Todas as respostas JSON seguem o padrão (chave `ok`, NÃO `success` — corrigido
em 2026-08-18, confirmado por leitura direta de `ApiController.php`; toda
versão anterior deste arquivo dizia `success` e estava errada):
```json
{
  "ok": true,
  "data": { ... }
}
```
Ou em caso de erro:
```json
{
  "ok": false,
  "error": "mensagem de erro"
}
```

## Autenticação
- Baseada em sessão (cookie). Não há tokens JWT ou API keys.
- Middleware `Auth` verifica `$_SESSION['user_id']` — redireciona para `/login` se ausente.

## Autorização
- Middleware `Rbac` verifica permissão específica da ação.
- Permissões definidas em seed.sql (`cliente.*`, `pedido.*`, `project.*`, `user.*`, `report.*`, `dashboard.view`, `admin.view`).
- Permissões cacheadas em sessão via `RbacService::loadForRole()`.

## Observações
- Não há suporte a paginação, ordenação ou campos específicos (select fields).
- Não há versionamento de API (sem prefixo `/v1/`).
- Não há rate limiting ou throttling.
- Os endpoints inativos em `routes/pedidos.php` só funcionariam se o módulo Pedidos fosse reativado.
