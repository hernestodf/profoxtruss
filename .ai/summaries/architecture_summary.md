# Architecture Summary

## Fluxo da Request
```
public/index.php
  ├── config.php (env, sessão, PDO singleton)
  ├── app/helpers.php (funções globais)
  ├── Autoloader PSR-4-like
  ├── ErrorHandler
  ├── routes.php (registra rotas no Router estático)
  ├── Router::match() (resolve controller+middlwares+params)
  ├── Middleware pipeline (Auth → Rbac)
  └── Controller::method()
```

## Camadas
- **Controllers** (`app/controllers/`): 9 controllers, herdam `BaseController` com helpers
- **Repositories** (`app/repositories/`): 6 repositories, PDO via construtor opcional
- **Services** (`app/services/`): `RbacService` — RBAC com cache de sessão
- **Middlewares** (`app/middlewares/`): `Auth` (sessão) e `Rbac` (autorização por ação)
- **Views** (`app/views/`): 10 subdiretórios, template engine `Layout` com blocos
- **Models** (`app/models/`): não utilizado (repositories fazem queries diretas)

## Rotas
- Arquivo central `routes.php` inclui módulos individuais em `routes/*.php`
- `Router` estático com métodos: `get()`, `post()`, `put()`, `delete()`, `any()`, `group()` (aninhável)
- Middlewares por grupo: `['Auth']` ou `['Rbac:action']`
- API em `/api/*` com resposta JSON

## Banco
- SQLite via PDO, caminho em `DB_PATH` no `.env`
- Singleton `getPDO()` — uma conexão por request
- Migrations em `database/migration.sql`, seed em `database/seed.sql`

## Design System
- 19+ Web Components customizados em `public/assets/js/components/`
- CSS modular com `@import` em `public/assets/css/`
- Tema neon com CSS custom properties
- Layout único via `app/views/layouts/main.php` com sidebar, navbar, drawer

## Limitações Conhecidas
- PDO singleton: impossível múltiplas conexões no mesmo request
- Sem suporte a `PUT`/`DELETE` nativo em formulários HTML (só GET/POST)
- `bootstrap.sh` não existe (referenciado em AGENTS.md e CLAUDE.md)

## Topologia de Deploy (2026-08-18)
- Ambiente de dev local (Apache, `/var/www/html/profoxtruss`) e produção real
  (VPS CyberPanel/OpenLiteSpeed, `/home/sisloc.online/public_html/profoxtruss`,
  domínio `sisloc.online/profoxtruss`) são MÁQUINAS DIFERENTES — editar local
  não reflete em produção. Deploy é manual (scp arquivo por arquivo + backup
  prévio no servidor), sem CI/CD nem script de deploy. Ver
  `.ai/context/deployment.md` para o procedimento completo e credenciais de
  acesso SSH.
