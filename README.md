# ProFoxTruss

Sistema web de engenharia para **projetar, calcular e gerenciar estruturas treliçadas Box Truss** (perfil Q30 e Perfil PM5 em alumínio) — usado por empresas de montagem de palcos, stands e estruturas modulares para eventos.

Editor visual **2D/3D sincronizado**: monte a estrutura arrastando peças do catálogo, com colisão real entre componentes, snap magnético, raio-x e visualização tridimensional interativa — tudo rodando no navegador, sem plugin nenhum.

[![Playwright](https://github.com/hernestodf/profoxtruss/actions/workflows/playwright.yml/badge.svg)](https://github.com/hernestodf/profoxtruss/actions/workflows/playwright.yml)
![PHP](https://img.shields.io/badge/PHP-8.4-777BB4?logo=php&logoColor=white)
![SQLite](https://img.shields.io/badge/SQLite-3-003B57?logo=sqlite&logoColor=white)
![Alpine.js](https://img.shields.io/badge/Alpine.js-3-8BC0D0?logo=alpinedotjs&logoColor=white)
![Three.js](https://img.shields.io/badge/Three.js-3D-black?logo=threedotjs&logoColor=white)

## Demo

🔗 **[sisloc.online/profoxtruss](https://sisloc.online/profoxtruss)** — instância pública de demonstração (sem dados reais de cliente).

## Destaques técnicos

- **Editor 2D/3D sincronizado do zero**: [Fabric.js](http://fabricjs.com/) pro canvas 2D e [Three.js](https://threejs.org/) pro 3D, compartilhando o mesmo modelo de dados — cada uma das 5 views 2D (frontal, fundo, lateral esq/dir, planta) projeta e trava a metade ambígua do espaço 3D pra evitar seleção incorreta (ver `_isLockedForView`/`_applyViewLocks` em [`app/views/home/index.php`](app/views/home/index.php)).
- **Colisão real entre peças** com resolução por busca binária — ao encostar duas peças, desliza pela trajetória do gesto até a fração mais próxima que não sobrepõe nada, em vez de simplesmente cancelar o movimento.
- **MVC próprio em PHP puro**, sem framework: roteador com grupos/middlewares aninháveis, template engine com herança de blocos, repository pattern com DI manual — ver [`app/utils/Router.php`](app/utils/Router.php) e [`app/utils/Layout.php`](app/utils/Layout.php).
- **RBAC** (roles + permissões por ação) com cache de sessão, e **CSRF** tanto em formulários quanto nos endpoints JSON da API (token via header, validado em `csrfValidateJson()`).
- **Suíte E2E com Playwright + CI no GitHub Actions** — cobrindo desde smoke tests até regressão de bugs reais de interação no canvas (ver [`tests/`](tests/)).

## Stack

| Camada | Tecnologia |
|---|---|
| Backend | PHP 8.4, MVC próprio (sem framework), SQLite via PDO |
| Frontend | Alpine.js, Tailwind CSS, Fabric.js (canvas 2D), Three.js (3D) |
| Design System | Web Components customizados, CSS modular com temas |
| Testes | Playwright (E2E: smoke + regressão) |
| CI/CD | GitHub Actions |

## Funcionalidades

- **Calculadora Box Truss** — projetista visual 2D/3D com catálogo de peças (Box Truss Q30 + Perfil PM5), colisão real, raio-x, snap magnético, zoom, cotas dimensionais.
- **Peças & Estoque** — catálogo de componentes com controle de estoque.
- **Projetos** — salvar/carregar/duplicar projetos (componentes persistidos como JSON).
- **Autenticação & RBAC** — login com sessão, 3 papéis (admin/editor/user), permissões granulares por ação.
- **Painel administrativo** — gestão de usuários e permissões.

## Arquitetura

```
public/index.php  (entry point)
 ├── config.php        → PDO singleton, sessão, env
 ├── routes.php        → registra rotas de routes/*.php
 ├── Router            → registro/match, grupos + middlewares aninháveis
 ├── middlewares/       → Auth (sessão), Rbac (autorização)
 ├── controllers/       → herdam BaseController (view/json/redirect/csrf/validate)
 │    └── repositories/ → SQL direto via PDO, sem ORM
 │         └── services/ → RbacService (cache de sessão)
 └── Layout             → template engine com herança de blocos (set/block/slot)
      └── views/         → layouts, páginas, componentes
```

## Testes & CI

```bash
npm install
npx playwright install chromium
npm test                  # suíte completa
npm run test:smoke        # só smoke tests
npm run test:regression   # só regressão
```

O workflow em [`.github/workflows/playwright.yml`](.github/workflows/playwright.yml) roda a suíte a cada push/PR usando o servidor embutido do PHP (`php -S` + `public/router.php`) e um banco SQLite recriado do zero a partir de `database/migration.sql` + `database/seed.sql` — sem depender de nenhum ambiente externo.

## Governança & práticas de engenharia

Este projeto não tinha CI, testes automatizados nem histórico de git até esta rodada de trabalho. O que foi estabelecido:

- **Todo bug corrigido ganha um teste de regressão que prova o bug antes de provar o fix.** Não "parece certo" — o teste é rodado contra o código antigo (falha, reproduzindo o defeito) e contra o código corrigido (passa), nos dois sentidos, antes de qualquer commit. Exemplos reais em [`tests/regression/`](tests/regression): um bug de seleção incorreta no canvas 2D/3D, e uma falha de CSRF na API JSON.
- **CI como gate, não como enfeite**: o workflow sobe o app do zero (servidor + banco) a cada push/PR, sem depender de estado residual de nenhuma máquina — se passa no CI, passa em qualquer lugar.
- **Deploy auditável, não "just push it"**: sem pipeline de deploy automatizado ainda (gap conhecido, documentado), mas o processo manual é disciplinado — backup do arquivo original antes de qualquer alteração em produção, upload verificado por checksum (`md5sum` local vs. remoto), e smoke test real (login + páginas críticas) depois de cada mudança, nos dois ambientes (local e produção) antes de considerar o trabalho concluído.
- **Segurança tratada como parte do ciclo normal, não como auditoria à parte**: nesta mesma sessão, revisão de código encontrou e corrigiu CSRF ausente na API e exposição pública do banco SQLite (`.htaccess` não cobria a extensão) — corrigido e **verificado em produção**, incluindo confirmação de que o site continuou funcionando normalmente depois.
- **Acesso a produção é auditado, não automático**: mudanças em ambiente de produção exigem autorização explícita antes de qualquer leitura ou escrita remota — não é assumido por padrão.

## Rodando localmente

```bash
git clone git@github.com:hernestodf/profoxtruss.git
cd profoxtruss
cp .env.example .env

# Banco (schema + dados de exemplo)
sqlite3 database/database.sqlite < database/migration.sql
sqlite3 database/database.sqlite < database/seed.sql

# Servidor de desenvolvimento
php -S localhost:8000 -t public public/router.php
```

Acesse `http://localhost:8000/login` — usuário seed: `admin@exemplo.com` / `admin123` (mesmas credenciais de demonstração exibidas na própria tela de login).

## Estrutura do projeto

```
app/
├── controllers/     Auth, Home, Dash, Admin, Api, Peca, Cliente, Pedido
├── repositories/    User, Cliente, Pedido, Produto, Project, Permission
├── middlewares/      Auth, Rbac
├── services/         RbacService
├── utils/             Router, Layout, ErrorHandler
└── views/             layouts/, home/ (calculadora), pecas/, admin/, auth/
routes/                app, auth, admin, pecas, api (um arquivo por módulo)
tests/
├── smoke/             disponibilidade básica (login, rotas protegidas, assets)
└── regression/        bugs reais encontrados e corrigidos, cobertos por teste
public/                document root (assets estáticos, index.php, router.php)
```

---

<sub>Desenvolvido por [Hernesto](https://github.com/hernestodf).</sub>
