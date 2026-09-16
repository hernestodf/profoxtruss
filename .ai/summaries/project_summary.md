# Project Summary — ProFoxTruss

## O que é
Sistema web para engenharia de treliças Box Truss (perfil Q30) e, desde 2026-08-17,
também do Perfil PM5 (alumínio 50x50mm, montante/travessa). Permite projetar, calcular
e gerenciar estruturas treliçadas com visualização 2D (Fabric.js) e 3D (Three.js),
colisão real entre peças, controle de estoque de peças e cadastro de projetos.

## Stack
- **Backend**: PHP 8.4 — MVC puro (sem framework), SQLite via PDO
- **Frontend**: Alpine.js, Tailwind CSS (CDN), Fabric.js (canvas 2D), Three.js (visualização 3D)
- **Design System**: Web Components customizados (19+ componentes UI), CSS modular com temas neon

## Módulos

| Módulo | Status | Descrição |
|--------|--------|-----------|
| Auth | Ativo | Login/logout com sessão, password_verify |
| Dashboard | Ativo | Estatísticas de projetos, peças, estoque |
| Admin/Usuários | Ativo | CRUD de usuários, gerenciamento de permissões RBAC |
| Calculadora Box Truss | Ativo | Projetista de treliças 2D/3D com catálogo de peças (Box Truss Q30 + Perfil PM5), colisão real, raio-x, snap magnético, zoom |
| Peças & Estoque | Ativo | Catálogo de peças Box Truss com controle de estoque |
| API | Ativo | Endpoints JSON para peças, projetos (CRUD completo) |
| Clientes | Desativado | CRUD completo existe (controller, repository, views, routes) mas comentado em `routes.php` |
| Pedidos | Desativado | CRUD completo existe mas comentado; contém rotas `/api` adicionais (busca clientes/produtos) |

## Arquitetura
- Entry point único: `public/index.php`
- Roteador estático próprio (`Router`) com suporte a grupos aninhados e middlewares
- Template engine própria (`Layout`) com herança de blocos
- Middleware pipeline: `Auth` (sessão) -> `Rbac` (autorização)
- Repository pattern com DI manual (construtor recebe PDO opcional)
- PDO singleton por request (`getPDO()` em `config.php`)
- RBAC com cache de sessão via `RbacService`

## Estado
- Sistema funcional em produção real: `https://sisloc.online/profoxtruss` (VPS
  CyberPanel/OpenLiteSpeed remoto — NÃO confundir com o ambiente Apache local
  de desenvolvimento; ver `.ai/context/deployment.md` para os dois ambientes
  e o fluxo de deploy manual, descoberto/documentado em 2026-08-18)
- Módulos Clientes e Pedidos com código completo (controllers, repositories, views, routes) mas desativados
- Calculadora é o módulo principal com ~5300 linhas na view (monólito Alpine + Fabric.js + Three.js) — cresceu bastante desde a primeira medição (~2662 linhas)
- Bootstrap automático não configurado (bootstrap.sh não existe)
