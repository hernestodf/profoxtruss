# Regras: Performance

## Banco de Dados
- `getPDO()` é singleton — não criar múltiplas conexões.
- SQLite lida bem com leitura concorrente mas escrita é serializada. Operações de escrita devem ser rápidas.
- `INSERT OR IGNORE` usado em seeds para evitar duplicatas em re-execução.
- Transações agrupam múltiplas operações (ex: `PedidoRepository::create()` com pedido + itens).

## Sessão e Cache
- RBAC cacheia permissões em `$_SESSION['_rbac_permissions']`.
- Após modificar permissões via admin, chamar `RbacService::invalidate()` para limpar cache.
- Cache é por request — não há cache distribuído (Redis/Memcached).

## Frontend
- Alpine.js e Tailwind via CDN — sem bundle local.
- Fabric.js e Three.js carregados via CDN — payload grande (~500KB total).
- Web Components são leves e registrados uma vez.
- A calculadora (home/index.php:2662 linhas) é o maior gargalo de performance — contém todo o JS inline.

## Recomendações
- Se a calculadora ficar lenta, considerar:
  - Extrair JS inline para arquivo separado
  - Implementar virtual scrolling para catálogo de peças
  - Otimizar renderização Fabric.js (objectCaching, requestRenderAll)
- Monitorar tempo de resposta via `appLog()`.
- Arquivo `database/database.sqlite` cresce com projetos; considerar VACUUM periódico.
