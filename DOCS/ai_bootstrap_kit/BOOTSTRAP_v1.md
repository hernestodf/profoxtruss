# BOOTSTRAP — Sistema de Inteligência Persistente para Projetos

> **O que é:** Este arquivo é o "sistema operacional cognitivo" de qualquer projeto.
> Ele transforma o repositório em um **cérebro persistente**: memória estruturada + agentes
> especializados + Git como memória temporal + documentação viva.
>
> **Objetivo central:** que o conhecimento sobre o projeto **sobreviva** à troca de sessão,
> troca de IA (Claude Code, Kilo, Cline, GPT, Ollama) e troca de desenvolvedor.
>
> **Como usar:** copie a pasta `.ai/` (gerada a partir deste arquivo) para a raiz do projeto,
> cole o **PROMPT DE PRIMEIRA EXECUÇÃO** (seção 3) na IA, e depois use o **PROMPT DIÁRIO** (seção 4).

---

## ÍNDICE

1. Conceito: as duas camadas
2. Estrutura completa de diretórios `.ai/`
3. PROMPT DE PRIMEIRA EXECUÇÃO (first run / bootstrap)
4. PROMPT DIÁRIO (evolução contínua)
5. Regras globais de classificação de confiança
6. Integração com Git (hooks)
7. Agentes — definição completa + prompt individual
8. Scripts (`deploy.sh`, `diagnostics.sh`, `log_scan.sh`, `healthcheck.sh`)
9. Templates de arquivos (context, memory, monitoring, reports, rules, tasks, summaries)
10. Integração com ferramentas (Claude Code, Kilo, Cline, Ollama)
11. Especialização PHP / ISP / Legado
12. Checklist final de instalação

---

## 1. CONCEITO: AS DUAS CAMADAS

| Camada | Nome | Quando roda | Muda com frequência? |
|--------|------|-------------|----------------------|
| 1 | **Blueprint permanente** | Sempre presente no repo | Quase nunca |
| 2 | **Bootstrap inicial** | Só na 1ª vez que a IA entra | Uma vez (regenerável) |

- **Camada 1 (Blueprint permanente):** define memória, agentes, Git, documentação, observabilidade, deploy, auditoria, segurança, banco, arquitetura e aprendizado contínuo. É a "infraestrutura".
- **Camada 2 (Bootstrap inicial):** executada quando a IA entra no projeto pela primeira vez. Mapeia tudo, cria a memória inicial, gera os relatórios e estabelece o *baseline* arquitetural.

> Princípio de ouro: **o projeto presente** = estado atual. **O Git** = histórico completo.
> **A IA** = cérebro que analisa a evolução cruzando os dois.

---

## 2. ESTRUTURA COMPLETA DE DIRETÓRIOS

```
projeto/
├── .git/
├── .ai/
│   │
│   ├── README.md                    # explica o sistema .ai/ a humanos e IAs
│   ├── INDEX.md                     # índice/mapa de tudo que existe em .ai/
│   │
│   ├── bootstrap/
│   │   ├── first_run.md             # prompt da primeira execução (seção 3)
│   │   ├── daily_run.md             # prompt diário (seção 4)
│   │   ├── ai_instructions.md       # como qualquer IA deve se comportar aqui
│   │   ├── onboarding.md            # passo a passo de onboarding de IA/dev
│   │   └── project_discovery.md     # checklist de descoberta do projeto
│   │
│   ├── context/
│   │   ├── stack.md                 # linguagens, frameworks, versões
│   │   ├── architecture.md          # arquitetura geral e camadas
│   │   ├── business_rules.md        # regras de negócio
│   │   ├── database.md              # esquema, tabelas, relações
│   │   ├── api_patterns.md          # padrões de API/endpoints
│   │   ├── integrations.md          # integrações externas
│   │   ├── deployment.md            # como o deploy funciona
│   │   └── terminology.md           # glossário do domínio
│   │
│   ├── memory/
│   │   ├── decisions.md             # decisões arquiteturais (ADR-like)
│   │   ├── learned_patterns.md      # padrões aprendidos pela IA
│   │   ├── mistakes.md              # erros já cometidos (não repetir)
│   │   ├── bugs.md                  # bugs históricos
│   │   ├── improvements.md          # melhorias aplicadas
│   │   ├── deprecated.md            # o que foi descontinuado
│   │   └── refactors.md             # refatorações feitas e motivo
│   │
│   ├── monitoring/
│   │   ├── production_errors.md
│   │   ├── frontend_errors.md
│   │   ├── php_errors.md
│   │   ├── api_health.md
│   │   ├── performance.md
│   │   ├── deployments.md
│   │   └── incidents.md
│   │
│   ├── reports/
│   │   ├── dead_php.md
│   │   ├── dead_js.md
│   │   ├── duplicated_code.md
│   │   ├── unused_routes.md
│   │   ├── unused_dependencies.md
│   │   ├── database_cleanup.md
│   │   ├── architecture_review.md
│   │   └── security_review.md
│   │
│   ├── agents/
│   │   ├── backend.md
│   │   ├── frontend.md
│   │   ├── database.md
│   │   ├── reviewer.md
│   │   ├── security.md
│   │   ├── performance.md
│   │   ├── memory_manager.md
│   │   ├── dead_code_php.md
│   │   ├── dead_code_js.md
│   │   ├── duplicate_code.md
│   │   ├── route_auditor.md
│   │   ├── dependency_auditor.md
│   │   ├── database_auditor.md
│   │   ├── deployment_manager.md
│   │   ├── monitoring_agent.md
│   │   ├── documentation_agent.md
│   │   │
│   │   │   # especializados (PHP / ISP / legado)
│   │   ├── php_legacy_specialist.md
│   │   ├── isp_business_rules.md
│   │   ├── mikrotik_integrations.md
│   │   ├── mysql_optimizer.md
│   │   ├── ui_tailwind_specialist.md
│   │   └── production_incident_agent.md
│   │
│   ├── rules/
│   │   ├── coding_style.md
│   │   ├── security.md
│   │   ├── performance.md
│   │   ├── api_rules.md
│   │   ├── database_rules.md
│   │   ├── ui_rules.md
│   │   └── naming.md
│   │
│   ├── tasks/
│   │   ├── roadmap.md
│   │   ├── backlog.md
│   │   ├── current.md
│   │   ├── bugs_to_fix.md
│   │   └── future_ideas.md
│   │
│   ├── summaries/
│   │   ├── project_summary.md
│   │   ├── architecture_summary.md
│   │   ├── business_summary.md
│   │   └── current_state.md
│   │
│   └── scripts/
│       ├── deploy.sh
│       ├── rollback.sh
│       ├── diagnostics.sh
│       ├── log_scan.sh
│       └── healthcheck.sh
│
├── .githooks/                       # hooks versionados (ver seção 6)
│   ├── pre-commit
│   └── post-commit
│
├── api/
├── assets/
├── components/
└── views/
```

---

## 3. PROMPT DE PRIMEIRA EXECUÇÃO (first run)

> Cole isto na IA (Claude Code / Kilo / Cline / GPT / Ollama) na **primeira vez** que ela entra no projeto.
> Salve também em `.ai/bootstrap/first_run.md`.

```
Você está entrando neste projeto pela PRIMEIRA VEZ.

OBJETIVO:
Criar a inteligência persistente do projeto dentro da pasta .ai/.

REGRAS INEGOCIÁVEIS:
- NUNCA remova código automaticamente.
- NUNCA invente fatos: se não tem certeza, marque como "a confirmar".
- SEMPRE classifique achados por nível de confiança (ver tabela abaixo).
- SEMPRE preserve histórico, decisões e contexto.
- TODA documentação em Markdown, dentro de .ai/.
- Em sistema legado, assuma que código "sem referência" pode ser regra de
  negócio executada por rotina/cron/mensalmente. Marque como SUSPEITO, não MORTO.

ETAPAS DE DESCOBERTA:
1. Leia toda a estrutura de diretórios do projeto.
2. Identifique stack, arquitetura, linguagens, frameworks e versões.
3. Identifique APIs, endpoints, rotas e serviços.
4. Identifique banco de dados (tabelas, views, procedures, relações).
5. Identifique integrações externas (pagamento, MikroTik, e-mail, etc.).
6. Identifique regras de negócio principais.
7. Leia package.json, composer.json, Dockerfile, .env.example, configs e scripts.
8. Analise o Git: `git log --oneline -50`, branches e datas de criação/alteração
   de arquivos com `git log --all --format='%ci' -1 -- <arquivo>`.

CRIE OU ATUALIZE:
- .ai/context/*       (stack, architecture, database, api_patterns, integrations,
                        business_rules, deployment, terminology)
- .ai/summaries/*     (project_summary, architecture_summary, business_summary,
                        current_state)
- .ai/memory/*        (decisions, learned_patterns — apenas o que descobrir com base real)
- .ai/rules/*         (deduza padrões de estilo/segurança já existentes no código)

GERE RELATÓRIOS INICIAIS (baseline) em .ai/reports/:
- dead_php.md             (funções/arquivos/classes/endpoints PHP sem referência)
- dead_js.md              (funções/módulos/arquivos JS não importados)
- unused_dependencies.md  (deps em composer.json/package.json sem uso real)
- unused_routes.md        (rotas/endpoints sem chamadas)
- duplicated_code.md      (lógicas duplicadas com % de similaridade)
- architecture_review.md  (gargalos, acoplamentos, riscos)
- security_review.md      (SQL injection, secrets em código, validação, etc.)
- database_cleanup.md     (tabelas/views/procedures candidatas a arquivamento)

CLASSIFICAÇÃO DE CONFIANÇA (use em todo relatório):
  95% = provavelmente morto / problema confirmado
  80% = revisar
  60% = suspeito
  40% = manter (provavelmente em uso)

FORMATO DE CADA ACHADO:
  ## <arquivo ou item>
  Motivo: <por que foi marcado>
  Última referência: <commit/data ou "não encontrada">
  Confiança: <%>
  Recomendação: <revisar / arquivar / refatorar / manter>

ENTREGÁVEL FINAL:
- Atualize .ai/INDEX.md com o mapa do que foi criado.
- Escreva em .ai/summaries/current_state.md um resumo do estado atual do projeto.
- NÃO altere o código-fonte nesta primeira execução. Apenas analise e documente.
```

---

## 4. PROMPT DIÁRIO (evolução contínua)

> Use no dia a dia, após a primeira execução. Salve em `.ai/bootstrap/daily_run.md`.

```
Leia a estrutura .ai/ para recuperar o contexto.

Analise as alterações recentes usando Git:
- git diff HEAD~1          (último commit)
- git log --oneline -10    (commits recentes)
- git diff main..<branch>  (se estiver comparando branches)

Com base no diff:
1. Atualize .ai/memory/learned_patterns.md com novos padrões detectados.
   Ex.: se viu `axios.post()` virar `fetch()`, registre "projeto migrando para Fetch API".
2. Atualize .ai/memory/decisions.md se houve decisão arquitetural.
3. Atualize .ai/context/* se a stack/arquitetura/banco mudou.
4. Atualize .ai/summaries/current_state.md.
5. Detecte: inconsistências, regressões, código morto novo, oportunidades de
   refatoração e problemas de performance/segurança.
6. Atualize relatórios em .ai/reports/ apenas onde houver mudança.

Consolide APENAS informação relevante. Não duplique. Não invente.
Não remova código automaticamente.
```

---

## 5. REGRAS GLOBAIS DE CLASSIFICAÇÃO DE CONFIANÇA

Todo agente de auditoria usa a mesma escala:

| Confiança | Significado | Ação sugerida |
|-----------|-------------|---------------|
| 95% | Provavelmente morto / problema confirmado | Revisar e remover após validação humana |
| 80% | Forte indício | Revisar com atenção |
| 60% | Suspeito | Investigar contexto |
| 40% | Provavelmente em uso | Manter |

> **Regra anti-falso-positivo (crítica em legado):** código sem referência estática
> pode ser chamado por cron, webhook, rota dinâmica, `call_user_func`, include
> condicional ou rotina mensal. Nunca classifique como 95% sem checar Git + execução.

---

## 6. INTEGRAÇÃO COM GIT (HOOKS)

Hooks versionados em `.githooks/` (para sobreviver a clones). Ative com:

```bash
git config core.hooksPath .githooks
chmod +x .githooks/*
```

### `.githooks/pre-commit`

```bash
#!/bin/bash
# Roda ANTES do commit: revisão rápida de segurança e padrões.
echo "🔍 pre-commit: revisão de segurança e padrões..."

# Bloqueia secrets óbvios
if git diff --cached | grep -E "(password|secret|api_key)\s*=\s*['\"][^'\"]+" ; then
  echo "❌ Possível secret hardcoded no diff. Revise antes de commitar."
  exit 1
fi

# (Opcional) chamada a uma IA local/CLI para revisar o diff:
# claude "Revise o diff staged: segurança, padrões e inconsistências. Apenas alerte."

exit 0
```

### `.githooks/post-commit`

```bash
#!/bin/bash
# Roda DEPOIS do commit: IA lê a mudança, aprende e documenta.
echo "🧠 post-commit: atualizando memória .ai/ ..."

# Exemplo com Claude Code CLI (ajuste para sua ferramenta: kilo/cline/ollama):
# claude "Analise o último commit (git diff HEAD~1) e atualize .ai/memory/ e .ai/summaries/current_state.md"

exit 0
```

> Comandos Git essenciais para a IA: `git diff`, `git diff HEAD~1`, `git diff main..dev`,
> `git log`, `git log --all`, `git status`, `git branch`, `git checkout -b feature/x`.

### Convenção de commits (a IA aprende muito melhor)

```
feat: adiciona autenticação JWT
refactor: padroniza APIs em Fetch
fix: corrige login mobile
chore: atualiza dependências
docs: atualiza .ai/context/architecture.md
```

### Fluxo de branches

| Branch | Uso |
|--------|-----|
| `main` | produção |
| `dev` | desenvolvimento |
| `feature/*` | funcionalidades |
| `hotfix/*` | correções urgentes |

---

## 7. AGENTES — DEFINIÇÃO + PROMPT INDIVIDUAL

> Cada arquivo em `.ai/agents/` segue o mesmo formato: **Objetivo → Entradas → Saída → Prompt**.
> Abaixo está o conteúdo de cada um. O agente nunca apaga código; sempre classifica por confiança.

### `agents/backend.md`
- **Objetivo:** implementar/ajustar lógica de servidor seguindo `rules/` e `context/`.
- **Entradas:** `context/architecture.md`, `context/api_patterns.md`, `rules/*`.
- **Saída:** código + atualização de `memory/decisions.md`.
- **Prompt:** `"Você é o agente Backend. Siga rules/ e api_patterns.md. Implemente a tarefa atual de tasks/current.md, registre decisões em memory/decisions.md e nunca quebre contratos de API existentes."`

### `agents/frontend.md`
- **Objetivo:** implementar UI consistente com `rules/ui_rules.md` e padrão Tailwind.
- **Saída:** componentes + atualização de `learned_patterns.md`.
- **Prompt:** `"Você é o agente Frontend. Use Tailwind e os padrões de ui_rules.md. Reutilize componentes existentes antes de criar novos."`

### `agents/database.md`
- **Objetivo:** alterações de schema seguras e versionadas.
- **Prompt:** `"Você é o agente Database. Antes de qualquer ALTER, leia context/database.md e database_rules.md. Gere migration reversível e registre em memory/decisions.md."`

### `agents/reviewer.md`
- **Objetivo:** revisar PRs/diffs.
- **Prompt:** `"Você é o Reviewer. Analise o diff: segurança, padrões, performance, regressões. Aprove, peça ajustes ou bloqueie. Não altere código, apenas comente."`

### `agents/security.md`
- **Objetivo:** auditoria de segurança.
- **Prompt:** `"Você é o agente Security. Procure SQL injection, XSS, secrets hardcoded, validação ausente, auth fraca. Gere reports/security_review.md com confiança e correção sugerida."`

### `agents/performance.md`
- **Prompt:** `"Você é o agente Performance. Identifique N+1 queries, loops custosos, falta de índice, payloads grandes. Atualize monitoring/performance.md."`

### `agents/memory_manager.md`
- **Objetivo:** consolidar e limpar a memória (evita inchaço).
- **Prompt:** `"Você é o Memory Manager. Leia memory/ e summaries/. Consolide o que é relevante, remova redundância, mantenha decisões e padrões. Nunca apague decisões arquiteturais."`

### `agents/dead_code_php.md`
- **Objetivo:** localizar funções sem uso, includes abandonados, classes sem referência, controllers e endpoints PHP mortos, queries repetidas.
- **Saída:** `reports/dead_php.md`.
- **Prompt:**
```
Você é o agente Dead Code PHP. Procure:
- funções nunca chamadas
- arquivos/includes nunca carregados
- classes sem referência
- endpoints/controllers sem chamadas
Cruze com git log para ver última alteração. Classifique por confiança.
NUNCA apague. Em legado, considere cron/rotinas mensais (marque SUSPEITO).
Saída em reports/dead_php.md no formato padrão de achado.
```

### `agents/dead_code_js.md`
- **Objetivo:** funções não chamadas, eventos abandonados, módulos sem import, arquivos não carregados, `fetch` antigos.
- **Saída:** `reports/dead_js.md`.
- **Prompt:** `"Você é o agente Dead Code JS. Liste arquivos JS sem import, funções não referenciadas e handlers órfãos. Cruze com git. Classifique por confiança. Saída em reports/dead_js.md."`

### `agents/duplicate_code.md`
- **Objetivo:** detectar lógica duplicada (ex.: `buscarCliente()` vs `getCliente()`).
- **Saída:** `reports/duplicated_code.md` com % de similaridade e localização.
- **Prompt:** `"Você é o agente Duplicate Code. Encontre funções/blocos com mesma intenção. Informe arquivo:linha de cada ocorrência e similaridade %. Sugira consolidação. Não altere código."`

### `agents/route_auditor.md`
- **Objetivo:** rotas/endpoints sem chamadas (PHP, Apache, Nginx, APIs).
- **Saída:** `reports/unused_routes.md`.
- **Prompt:** `"Você é o agente Route Auditor. Liste endpoints sem referência no frontend/integrações. Ex.: /api/old_login.php. Confiança + última referência. Saída em reports/unused_routes.md."`

### `agents/dependency_auditor.md`
- **Objetivo:** deps em `composer.json`/`package.json` sem uso.
- **Saída:** `reports/unused_dependencies.md`.
- **Prompt:** `"Você é o agente Dependency Auditor. Para cada dependência, busque uso real no código. Liste as sem referência com 'Última referência: não encontrada'. Confiança por item."`

### `agents/database_auditor.md`
- **Objetivo:** tabelas/views/procedures sem uso.
- **Saída:** `reports/database_cleanup.md`.
- **Prompt:** `"Você é o agente Database Auditor. Liste tabelas/views/procedures sem acesso detectado no código. Ex.: tb_clientes_old. Marque como candidata a arquivamento com confiança. NÃO sugira DROP sem validação humana."`

### `agents/deployment_manager.md`
- **Objetivo:** orquestrar deploy/rollback.
- **Prompt:** `"Você é o Deployment Manager. Use scripts/deploy.sh e rollback.sh. Antes de deploy: healthcheck. Registre em monitoring/deployments.md."`

### `agents/monitoring_agent.md`
- **Objetivo:** ler logs e popular `monitoring/*`.
- **Prompt:** `"Você é o Monitoring Agent. Rode scripts/log_scan.sh, classifique erros (prod/php/frontend/api) e atualize monitoring/*. Abra incidente em monitoring/incidents.md se crítico."`

### `agents/documentation_agent.md`
- **Objetivo:** manter documentação viva sincronizada com o código.
- **Prompt:** `"Você é o Documentation Agent. Após mudanças, atualize context/ e summaries/ para refletirem o código atual. Nunca deixe doc divergir do real."`

### Especializados (PHP / ISP / legado)

#### `agents/php_legacy_specialist.md`
- **Prompt:** `"Você é o PHP Legacy Specialist. Entenda includes encadeados, globals, código procedural antigo e fluxos não óbvios. Antes de marcar algo como morto, procure chamadas dinâmicas (include condicional, call_user_func, cron). Documente armadilhas em memory/mistakes.md."`

#### `agents/isp_business_rules.md`
- **Prompt:**
```
Você é o ISP Business Rules. Compreenda o domínio de provedor de internet:
clientes, PPPoE, boletos, vencimentos, planos, bloqueios, desbloqueios,
suspensão por inadimplência e rotinas mensais.
NUNCA classifique como código morto rotinas que rodam por agendamento
(ex.: bloqueio diário, geração mensal de boletos). Marque como "executado por rotina".
Documente cada regra em context/business_rules.md.
```

#### `agents/mikrotik_integrations.md`
- **Prompt:** `"Você é o MikroTik Integrations. Mapeie integrações via API RouterOS (login PPPoE, bloqueio/liberação, filas, secrets). Documente endpoints e credenciais (referenciadas, não expostas) em context/integrations.md."`

#### `agents/mysql_optimizer.md`
- **Prompt:** `"Você é o MySQL Optimizer. Analise queries lentas, falta de índices, full table scans, JOINs custosos. Sugira índices e EXPLAIN. Atualize monitoring/performance.md e reports/database_cleanup.md."`

#### `agents/ui_tailwind_specialist.md`
- **Prompt:** `"Você é o UI Tailwind Specialist. Padronize componentes em Tailwind, elimine CSS legado não usado, garanta responsividade. Atualize rules/ui_rules.md e reports/dead_js/dead_css quando aplicável."`

#### `agents/production_incident_agent.md`
- **Prompt:** `"Você é o Production Incident Agent. Quando houver erro em produção, colete logs (log_scan.sh), identifique causa provável, proponha hotfix em branch hotfix/*, e registre o pós-morte em monitoring/incidents.md e memory/bugs.md."`

---

## 8. SCRIPTS

### `scripts/healthcheck.sh`
```bash
#!/bin/bash
# Verifica saúde básica do sistema.
echo "=== HEALTHCHECK $(date) ==="
php -v >/dev/null 2>&1 && echo "✅ PHP ok" || echo "❌ PHP ausente"
mysqladmin ping 2>/dev/null | grep -q alive && echo "✅ MySQL ok" || echo "⚠️ MySQL sem resposta"
curl -sf http://localhost/api/health >/dev/null && echo "✅ API ok" || echo "⚠️ API sem resposta"
df -h | awk 'NR==1 || /\/$/'
```

### `scripts/diagnostics.sh`
```bash
#!/bin/bash
# Coleta panorama do projeto para a IA.
echo "=== STACK ==="; [ -f composer.json ] && echo "composer.json ✔"; [ -f package.json ] && echo "package.json ✔"
echo "=== GIT ==="; git log --oneline -10
echo "=== ESTRUTURA ==="; find . -maxdepth 2 -type d -not -path '*/.git*' | sort
echo "=== PHP FILES ==="; find . -name "*.php" | wc -l
echo "=== JS FILES ==="; find . -name "*.js" | wc -l
```

### `scripts/log_scan.sh`
```bash
#!/bin/bash
# Varre logs em busca de erros recentes.
LOGS=(/var/log/apache2/error.log /var/log/nginx/error.log ./storage/logs/*.log)
for f in "${LOGS[@]}"; do
  [ -f "$f" ] || continue
  echo "=== $f (últimos erros) ==="
  grep -iE "error|fatal|exception|warning" "$f" | tail -n 30
done
```

### `scripts/deploy.sh`
```bash
#!/bin/bash
set -e
echo "🚀 Deploy iniciado $(date)"
bash scripts/healthcheck.sh
git pull origin main
# composer install --no-dev --optimize-autoloader   # se aplicável
# npm ci && npm run build                            # se aplicável
echo "✅ Deploy concluído. Registrando em .ai/monitoring/deployments.md"
echo "- $(date): deploy de $(git rev-parse --short HEAD)" >> .ai/monitoring/deployments.md
```

### `scripts/rollback.sh`
```bash
#!/bin/bash
set -e
PREV=${1:-HEAD~1}
echo "⏪ Rollback para $PREV"
git checkout "$PREV" -- .
bash scripts/healthcheck.sh
echo "- $(date): rollback para $PREV" >> .ai/monitoring/incidents.md
```

---

## 9. TEMPLATES DE ARQUIVOS

> Conteúdo inicial sugerido para os arquivos `.md` da estrutura.

### `context/stack.md`
```markdown
# Stack
- Linguagem(ns): PHP X.x, JavaScript
- Frontend: Tailwind CSS
- Banco: MySQL X.x
- Servidor: Apache/Nginx
- Dependências: (composer.json / package.json)
- Versões críticas: (a preencher)
```

### `context/business_rules.md`
```markdown
# Regras de Negócio
## Clientes
## Planos
## Boletos / Vencimentos
## Bloqueio / Desbloqueio
## Rotinas agendadas (cron/mensal)  <- IMPORTANTE p/ não marcar como código morto
```

### `memory/decisions.md`
```markdown
# Decisões Arquiteturais (ADR)
## [AAAA-MM-DD] Título da decisão
- Contexto:
- Decisão:
- Consequências:
- Commit relacionado:
```

### `memory/learned_patterns.md`
```markdown
# Padrões Aprendidos
- (ex.) Projeto padronizado em Fetch API; Axios descontinuado.
- (ex.) Nomeação de endpoints: /api/<recurso>/<acao>.
```

### `reports/dead_php.md` (formato de achado)
```markdown
# Relatório — Código Morto PHP

## /api/legacy/clientes_old.php
Motivo: Nenhuma referência encontrada no código.
Última referência: não encontrada (git log: alterado em 2024)
Confiança: 95%
Recomendação: revisar e arquivar após validação humana.
```

### `monitoring/incidents.md`
```markdown
# Incidentes
## [AAAA-MM-DD HH:MM] Título
- Sintoma:
- Causa provável:
- Ação tomada:
- Pós-morte / prevenção:
```

### `tasks/current.md`
```markdown
# Tarefa Atual
- Objetivo:
- Branch: feature/...
- Critérios de aceite:
- Agente responsável:
```

### `summaries/current_state.md`
```markdown
# Estado Atual do Projeto — [AAAA-MM-DD]
- Última mudança relevante:
- Branches ativas:
- Pendências críticas:
- Riscos conhecidos:
```

### `rules/security.md`
```markdown
# Regras de Segurança
- Sempre usar prepared statements (nunca concatenar SQL).
- Nunca commitar secrets; usar .env.
- Validar e sanitizar toda entrada do usuário.
- Auth obrigatória em endpoints sensíveis.
```

---

## 10. INTEGRAÇÃO COM FERRAMENTAS

A pasta `.ai/` é **agnóstica de ferramenta** — qualquer IA lê os mesmos `.md`.
Apenas o *gatilho* muda:

| Ferramenta | Como apontar para o sistema |
|------------|------------------------------|
| **Claude Code** | Adicione um `CLAUDE.md` na raiz: *"Sempre leia `.ai/` antes de agir. Siga `.ai/bootstrap/ai_instructions.md`."* |
| **Kilo / Cline** | Configure as *custom instructions* apontando para `.ai/bootstrap/ai_instructions.md`. |
| **Ollama (local)** | No hook `post-commit`, troque `claude "..."` por `ollama run <modelo> "<prompt do daily_run.md> + $(git diff HEAD~1)"`. |
| **GPT / outros** | Cole o conteúdo de `.ai/bootstrap/first_run.md` (1ª vez) ou `daily_run.md` (depois). |

### `bootstrap/ai_instructions.md` (conteúdo sugerido)
```markdown
# Instruções para qualquer IA neste projeto
1. SEMPRE leia .ai/ antes de qualquer ação (context, memory, rules, summaries).
2. NUNCA apague código automaticamente. Classifique por confiança.
3. Após mudanças, atualize memory/ e summaries/current_state.md.
4. Use commits semânticos (feat/fix/refactor/docs/chore).
5. Em legado/ISP: rotinas agendadas NÃO são código morto.
6. Toda decisão arquitetural vai para memory/decisions.md.
```

---

## 11. ESPECIALIZAÇÃO PHP / ISP / LEGADO

Para sistemas tipo provedor de internet (Supernet, Microleader e similares):

- **Não marque como morto** rotinas executadas por cron/mensalmente (geração de boletos, bloqueio por inadimplência).
- Mapeie integrações **MikroTik / RouterOS** em `context/integrations.md`.
- Documente o **fluxo comercial** (cliente → plano → boleto → vencimento → bloqueio → desbloqueio) em `context/business_rules.md`.
- Trate **PHP procedural antigo** com `php_legacy_specialist.md` antes de qualquer refatoração.
- Otimize banco com `mysql_optimizer.md` (índices, EXPLAIN, full scans).

---

## 12. CHECKLIST FINAL DE INSTALAÇÃO

1. Copie a pasta `.ai/` e `.githooks/` para a raiz do projeto.
2. `git init` (se ainda não houver) e `git config core.hooksPath .githooks`.
3. `chmod +x .githooks/* .ai/scripts/*`.
4. (Opcional) crie `CLAUDE.md` apontando para `.ai/bootstrap/ai_instructions.md`.
5. Rode o **PROMPT DE PRIMEIRA EXECUÇÃO** (seção 3) na sua IA.
6. Revise os relatórios em `.ai/reports/` (lembre: nada é apagado automaticamente).
7. No dia a dia, use o **PROMPT DIÁRIO** (seção 4).

> **Resultado:** um repositório que aprende sozinho, documenta sozinho, consolida
> conhecimento, mantém contexto e reduz retrabalho — um **cérebro persistente**
> que sobrevive à troca de IA, de sessão e de desenvolvedor.
