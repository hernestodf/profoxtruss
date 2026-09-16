# BOOTSTRAP v2 — Sistema de Inteligência Persistente com Memory Engine

> **O que mudou do v1 para o v2:** o v1 dava *onde* guardar memória. O v2 adiciona o
> **Memory Engine** — as regras que transformam "pasta que acumula texto" em algo que
> **aprende de forma confiável**. Quatro mecanismos novos: **Promoção, Conflito,
> Recuperação e Decaimento**. Mais 4 agentes "meta" que gerenciam o próprio aprendizado.
>
> Este arquivo é autossuficiente: contém toda a estrutura do v1 + o Memory Engine.

---

## ÍNDICE

1. Os 4 mecanismos do Memory Engine (o coração do v2)
2. Formato canônico de memória (frontmatter)
3. Ciclo de vida de um conhecimento (estados)
4. Estrutura de diretórios atualizada
5. Os 4 agentes meta (novos)
6. PROMPT de primeira execução (v2)
7. PROMPT diário (v2, com o loop completo)
8. Tabela completa de agentes
9. Git, hooks e o resto da infraestrutura (herdado do v1)

---

## 1. OS 4 MECANISMOS DO MEMORY ENGINE

O que separa **memória** de **mero acúmulo de texto** são quatro regras. Sem elas, a `.ai/`
incha, se contradiz e apodrece. Estas regras valem para TODA escrita em `.ai/memory/`.

### Mecanismo 1 — PROMOÇÃO (quando observação vira aprendizado)

Nada é "verdade do projeto" só porque foi visto uma vez. Todo conhecimento começa como
**observação** e só é promovido a **confirmado** com evidência repetida.

```
Regra de promoção:
- 1ª vez que algo é detectado  → status: observed,  evidence: 1
- evidência repetida em commit/contexto distinto → evidence += 1
- evidence >= 3  → status: confirmed   (agora a IA pode tratar como regra)
- evidence < 3   → permanece observed  (a IA cita como "tendência", não como regra)
```

> Exemplo: ver `fetch()` uma vez = tendência. Ver `fetch()` substituindo `axios` em 3
> commits distintos = padrão confirmado. Só então `learned_patterns.md` afirma
> "projeto padronizado em Fetch API".

### Mecanismo 2 — CONFLITO (qual versão vence)

Quando um conhecimento novo **contradiz** um confirmado, é **proibido duplicar**.
O antigo é rebaixado, não apagado (preserva histórico).

```
Regra de conflito:
- NUNCA manter duas afirmações contraditórias ativas.
- O conhecimento antigo recebe: status: deprecated  +  superseded_by: <id-novo>
- O conhecimento novo entra com evidence reiniciada (precisa reconfirmar).
- A justificativa da virada vai para memory/decisions.md (com commit).
```

> Exemplo: `pat-007` dizia "usa Fetch API". O projeto voltou ao Axios.
> `pat-007` vira `deprecated, superseded_by: pat-021`. Não existem duas verdades.

### Mecanismo 3 — RECUPERAÇÃO (o que ler, sem ler tudo)

"Sempre leia `.ai/`" não escala. Com a pasta grande, a IA precisa ler **só o relevante**.
Isso é resolvido por uma camada **quente** (sempre carregada) e **fria** (sob demanda),
guiada por **tags** no `INDEX.md`.

```
Memória QUENTE (sempre ler, é barata):
- summaries/current_state.md
- memory/learned_patterns.md  (apenas entradas status: confirmed)
- rules/  relevantes à tarefa

Memória FRIA (ler só quando a tarefa tiver a tag):
- context/database.md      → tarefa toca banco
- context/integrations.md  → tarefa toca MikroTik/pagamento
- reports/*                → tarefa de auditoria/limpeza
- memory/bugs.md, mistakes.md → tarefa de correção

Como decidir: o agente context_retriever lê INDEX.md, casa as tags da tarefa
atual (tasks/current.md) e carrega só os arquivos correspondentes.
```

### Mecanismo 4 — DECAIMENTO (o que esquecer / reconfirmar)

Conhecimento não verificado apodrece. Todo item tem `last_verified`. Se ficar velho
demais sem ser re-observado, ele **não é apagado** — vira `stale` e perde autoridade até
ser reconfirmado.

```
Regra de decaimento:
- Cada item tem last_verified: <data>.
- Se (hoje - last_verified) > 90 dias E não reapareceu em diffs → status: stale.
- Item stale NÃO pode ser usado como regra; deve ser reconfirmado pelo fact_verifier.
- Reconfirmação atualiza last_verified e devolve status: confirmed.
```

---

## 2. FORMATO CANÔNICO DE MEMÓRIA (frontmatter)

Todo item de memória/aprendizado/relatório usa **Markdown com frontmatter** — script lê o
topo (estruturado), IA lê o corpo (narrativo). É o que torna os 4 mecanismos automáticos.

```markdown
---
id: pat-021                  # identificador único e estável
type: pattern                # pattern | decision | bug | mistake | dead_code | rule
status: confirmed            # observed | confirmed | deprecated | stale
confidence: 90               # 0-100 (escala de confiança herdada do v1)
evidence: 4                  # quantas vezes foi observado (mecanismo 1)
first_seen: 2026-01-12       # data ou commit
last_verified: 2026-06-01    # mecanismo 4 (decaimento)
superseded_by: null          # id que substituiu este (mecanismo 2)
tags: [api, frontend, http]  # para recuperação seletiva (mecanismo 3)
source_commit: a1b2c3d       # de onde veio a evidência
---

# Projeto migrou para Fetch API

Substituição de `axios.post()` por `fetch()` observada em 4 commits distintos
entre jan e jun/2026. Tratar como padrão ao escrever novas chamadas HTTP.
Exceção conhecida: uploads grandes ainda usam XHR (ver bug-014).
```

---

## 3. CICLO DE VIDA DE UM CONHECIMENTO

```
        detectado pela 1ª vez
                │
                ▼
        ┌───────────────┐   evidence < 3
        │   observed     │◄──────────────┐
        └───────┬───────┘                │
                │ evidence >= 3          │ reobservado
                ▼                        │
        ┌───────────────┐                │
        │   confirmed    │────────────────┘
        └───┬───────┬───┘
   contradiz│       │ > 90 dias sem verificar
            ▼       ▼
   ┌────────────┐  ┌────────┐  fact_verifier reconfirma
   │ deprecated │  │ stale  │──────────────► confirmed
   └────────────┘  └────────┘
   (mantém histórico, nunca apaga)
```

---

## 4. ESTRUTURA DE DIRETÓRIOS (atualizada)

Igual ao v1, com adições em **negrito**:

```
.ai/
├── README.md
├── INDEX.md                        ← agora com TAGS por arquivo (recuperação)
│
├── bootstrap/
│   ├── first_run.md
│   ├── daily_run.md
│   ├── ai_instructions.md
│   ├── onboarding.md
│   ├── project_discovery.md
│   └── **memory_engine.md**         ← as 4 regras desta seção, p/ a IA consultar
│
├── context/        (stack, architecture, business_rules, database,
│                    api_patterns, integrations, deployment, terminology)
│
├── memory/
│   ├── decisions.md
│   ├── learned_patterns.md          ← itens em formato frontmatter (seção 2)
│   ├── mistakes.md
│   ├── bugs.md
│   ├── improvements.md
│   ├── deprecated.md                ← destino dos itens superseded (mecanismo 2)
│   ├── refactors.md
│   └── **observations.md**          ← staging: itens observed antes de promover
│
├── monitoring/     (production_errors, frontend_errors, php_errors,
│                    api_health, performance, deployments, incidents)   [JSONL]
│
├── reports/        (dead_php, dead_js, duplicated_code, unused_routes,
│                    unused_dependencies, database_cleanup,
│                    architecture_review, security_review)              [frontmatter]
│
├── agents/
│   │  (todos do v1: backend, frontend, database, reviewer, security,
│   │   performance, memory_manager, dead_code_php, dead_code_js,
│   │   duplicate_code, route_auditor, dependency_auditor, database_auditor,
│   │   deployment_manager, monitoring_agent, documentation_agent,
│   │   php_legacy_specialist, isp_business_rules, mikrotik_integrations,
│   │   mysql_optimizer, ui_tailwind_specialist, production_incident_agent)
│   │
│   ├── **orchestrator.md**          ← NOVO (meta): roteia a tarefa
│   ├── **context_retriever.md**     ← NOVO (meta): decide o que ler
│   ├── **knowledge_curator.md**     ← NOVO (meta): promoção + conflito
│   └── **fact_verifier.md**         ← NOVO (meta): decaimento + reconfirmação
│
├── rules/          (coding_style, security, performance, api_rules,
│                    database_rules, ui_rules, naming)
├── tasks/          (roadmap, backlog, current, bugs_to_fix, future_ideas)
├── summaries/      (project_summary, architecture_summary,
│                    business_summary, current_state)
└── scripts/        (deploy, rollback, diagnostics, log_scan, healthcheck)
```

---

## 5. OS 4 AGENTES META (novos)

### `agents/orchestrator.md`
- **Papel:** porta de entrada. Lê a tarefa, decide o pipeline de agentes.
- **Prompt:**
```
Você é o Orchestrator. Para a tarefa em tasks/current.md:
1. Identifique o domínio (backend, frontend, banco, infra, auditoria...).
2. Chame o context_retriever para carregar só a memória relevante.
3. Selecione os agentes de execução necessários (na ordem certa).
4. Ao final, acione knowledge_curator para gravar o que foi aprendido.
Você NÃO escreve código nem memória; você coordena.
```

### `agents/context_retriever.md`
- **Papel:** resolve o Mecanismo 3 (recuperação). Decide o que ler.
- **Prompt:**
```
Você é o Context Retriever. Dada a tarefa atual e suas tags:
1. SEMPRE carregue a memória quente: summaries/current_state.md +
   itens 'confirmed' de memory/learned_patterns.md + rules/ relevantes.
2. Leia INDEX.md, case as tags da tarefa e carregue apenas os arquivos frios
   correspondentes.
3. Ignore itens com status: stale ou deprecated (não são autoridade).
4. Devolva um resumo do contexto carregado para o orchestrator.
Objetivo: dar contexto suficiente gastando o mínimo de tokens.
```

### `agents/knowledge_curator.md`
- **Papel:** resolve Mecanismos 1 e 2 (promoção + conflito). Substitui/expande o memory_manager.
- **Prompt:**
```
Você é o Knowledge Curator. Após cada mudança relevante:
PROMOÇÃO (mec. 1):
- Para cada observação, procure item igual em memory/. Se existir, evidence += 1.
  Se não, crie em memory/observations.md com status: observed, evidence: 1.
- Quando evidence >= 3, mova para learned_patterns.md com status: confirmed.
CONFLITO (mec. 2):
- Se o novo conhecimento contradiz um 'confirmed', NÃO duplique.
- Marque o antigo: status: deprecated, superseded_by: <id-novo>, e mova p/ deprecated.md.
- Registre a justificativa em decisions.md com o commit.
Use sempre o formato frontmatter canônico. Atualize last_verified e source_commit.
Nunca apague histórico.
```

### `agents/fact_verifier.md`
- **Papel:** resolve o Mecanismo 4 (decaimento). Mantém a memória honesta.
- **Prompt:**
```
Você é o Fact Verifier. Periodicamente (ou antes de uma tarefa crítica):
1. Liste itens 'confirmed' com (hoje - last_verified) > 90 dias.
2. Verifique no código atual se ainda são verdade.
   - Verdadeiro  → atualize last_verified = hoje (mantém confirmed).
   - Falso       → trate como conflito (chame a regra do knowledge_curator).
   - Inconcluso  → status: stale (perde autoridade até reconfirmar).
Nunca apague. Apenas rebaixa ou revalida. Reporte o que mudou.
```

---

## 6. PROMPT DE PRIMEIRA EXECUÇÃO (v2)

```
Você está entrando neste projeto pela PRIMEIRA VEZ.
OBJETIVO: criar a inteligência persistente em .ai/, já com o Memory Engine ativo.

Antes de tudo, leia .ai/bootstrap/memory_engine.md e siga as 4 regras
(promoção, conflito, recuperação, decaimento) em TODA escrita de memória.

REGRAS INEGOCIÁVEIS:
- NUNCA remova código automaticamente. Classifique por confiança (95/80/60/40).
- NUNCA invente fatos. Tudo que descobrir entra como 'observed' (evidence: 1),
  NÃO como regra confirmada — a 1ª execução é só baseline.
- Use o formato frontmatter canônico em memory/ e reports/.
- Em legado/ISP, rotina agendada (cron/mensal) NÃO é código morto.

ETAPAS:
1. Mapeie estrutura, stack, arquitetura, APIs, banco, integrações, regras de negócio.
2. Leia composer.json, package.json, Dockerfile, configs.
3. Analise Git: git log --oneline -50, branches, datas dos arquivos.
4. Preencha context/*, summaries/*, rules/* (deduzidos do código existente).
5. Gere relatórios baseline em reports/* (frontmatter + confiança + last_verified).
6. Toda descoberta de padrão entra em memory/observations.md como 'observed'.
7. Preencha INDEX.md com TAGS por arquivo (habilita a recuperação seletiva).

NÃO altere o código nesta primeira execução. Apenas analise e documente.
```

---

## 7. PROMPT DIÁRIO (v2 — loop completo)

```
1. [orchestrator] Leia tasks/current.md e identifique domínio + tags.
2. [context_retriever] Carregue memória quente + arquivos frios pelas tags.
   Ignore itens stale/deprecated.
3. Analise as mudanças: git diff HEAD~1 (ou git diff main..<branch>).
4. Execute a tarefa com os agentes de domínio necessários.
5. [knowledge_curator] Para cada padrão/decisão detectado:
   - aplique PROMOÇÃO (evidence++ / promover em evidence>=3);
   - aplique CONFLITO (rebaixar antigo, nunca duplicar);
   - grave em formato frontmatter, atualize last_verified e source_commit.
6. [fact_verifier] Se algum item 'confirmed' tocado estiver > 90 dias, revalide.
7. Atualize summaries/current_state.md.
Consolide só o relevante. Nunca apague histórico. Nunca remova código.
```

---

## 8. TABELA COMPLETA DE AGENTES (v2)

| Categoria | Agentes |
|-----------|---------|
| **Meta (novos)** | orchestrator, context_retriever, knowledge_curator, fact_verifier |
| Execução | backend, frontend, database, deployment_manager |
| Qualidade | reviewer, security, performance |
| Auditoria | dead_code_php, dead_code_js, duplicate_code, route_auditor, dependency_auditor, database_auditor |
| Operação | monitoring_agent, documentation_agent, memory_manager |
| Especializados PHP/ISP | php_legacy_specialist, isp_business_rules, mikrotik_integrations, mysql_optimizer, ui_tailwind_specialist, production_incident_agent |

> Nota: o `memory_manager` do v1 continua, mas agora com papel reduzido (limpeza/dedup
> geral). A inteligência de promoção/conflito/decaimento fica nos agentes meta.

---

## 9. INFRAESTRUTURA HERDADA DO v1 (sem mudança)

Tudo isto permanece exatamente como no BOOTSTRAP.md original:

- **Git hooks** (`.githooks/pre-commit`, `post-commit`) — o `post-commit` agora deve
  chamar o **loop diário v2** (seção 7), não só "atualize memória".
- **Scripts** (`deploy.sh`, `rollback.sh`, `diagnostics.sh`, `log_scan.sh`, `healthcheck.sh`).
- **Convenção de commits** semânticos (feat/fix/refactor/docs/chore).
- **Fluxo de branches** (main/dev/feature/hotfix).
- **Templates** de context/, rules/, tasks/, summaries/.
- **Especialização PHP/ISP** (MikroTik, PPPoE, boletos, rotinas mensais).
- **Checklist de instalação** + ativação de hooks (`git config core.hooksPath .githooks`).

### Ajuste no `post-commit` para o v2
```bash
#!/bin/bash
echo "🧠 post-commit v2: rodando loop de aprendizado..."
# claude "Execute o PROMPT DIÁRIO v2 de .ai/bootstrap/daily_run.md sobre: $(git diff HEAD~1 --stat)"
exit 0
```

---

## RESUMO DO QUE O v2 RESOLVE

| Problema do v1 | Resolvido por |
|----------------|---------------|
| "Viu uma vez = virou regra" | Mecanismo 1 (promoção por evidência ≥ 3) |
| Memória se contradiz | Mecanismo 2 (conflito: rebaixa, não duplica) |
| "Ler tudo" não escala | Mecanismo 3 (recuperação quente/fria por tags) |
| Conhecimento apodrece | Mecanismo 4 (decaimento + reconfirmação) |
| Faltavam agentes de memória | 4 agentes meta (orchestrator, retriever, curator, verifier) |

Agora o sistema não só **guarda** conhecimento — ele **promove, reconcilia, recupera e
revalida**. É a diferença entre um arquivo de texto e uma memória que aprende.
