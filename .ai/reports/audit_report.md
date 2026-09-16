# AUDITORIA TÉCNICA PROFOXTRUSS

**Data:** 2026-06-10  
**Analista:** Sistema de Auditoria Automatizada  
**Versão do Código:** home/index.php + pecas/index.php + banco SQLite  
**Escopo:** Geração automática e manual de estruturas Box Truss Q30, visualização 2D/3D, lista de materiais.

---

## A) ERROS CRÍTICOS

### A1. Coluna não encurtada na base — sobreposição cubo × Q30

**Descrição:** Em `generateAutoLayout()`, a coluna Q30 vai de `baseY=0` até `topY-cuboHalf`. O topo é encurtado 0.15m para o cubo/grepo, mas a **base NÃO é encurtada**. O cubo da sapata está em `(col.x, 0, col.z)` — exatamente onde a coluna termina. Isso faz a Q30 da coluna penetrar o cubo.

```
Coluna:   y=0  ───────────────── y=topY-cuboHalf
Cubo:     y=0  ●
Sapata:   y=0  ──── (Q30 ao longo de Z)
```

**Impacto:** Interseção física impossível. O cubo e a Q30 da coluna ocupam o mesmo volume no espaço 3D. Na vida real, a Q30 encaixa NA FACE do cubo, não no centro.

**Solução:** Encurtar a coluna em `cuboHalf` também na base quando há sapata:
```javascript
generateTrussLine(startX, baseY + cuboHalf, 0, startX, topY - cuboHalf, 0, ..., rotZ=90);
```

**Prioridade:** ALTA

### A2. generateTrussLine — mesmo UID para múltiplas chamadas (colisão de timestamp)

**Descrição:** UIDs gerados como `'comp_' + Date.now() + '_' + Math.random()...` podem colidir se `generateTrussLine` for chamado em rápida sucessão (Date.now() retorna mesmo valor no mesmo ms).

**Impacto:** Componentes com mesmo UID causam comportamento imprevisível no `object:moving`, `object:modified`, `deleteSelected` e `calculateQuantitative`.

**Solução:** Incrementar um contador dedicado:
```javascript
_uidCounter: 0,
_uid() { return 'comp_' + (++this._uidCounter); }
```

**Prioridade:** ALTA

### A3. addSideConnectingBeams não adiciona sleeves

**Descrição:** `generateTrussLine()` adiciona sleeves entre segmentos consecutivos. `addSideConnectingBeams()` usa backtracking idêntico mas **NÃO adiciona sleeves** nas juntas entre as Q30 que preenchem a profundidade Z.

**Impacto:** Vigas lateriais (Z) não têm sleeves de emenda — estrutura incompleta no relatório quantitativo. Na montagem real, toda emenda de Q30 exige sleeve.

**Solução:** Adicionar sleeves em `addSideConnectingBeams()` ao final de cada segmento (exceto o último), igual ao `generateTrussLine()`.

**Prioridade:** ALTA

### A4. Grepo ausente no último ponto do sketch

**Descrição:** Em `generateFromSketch()`, grepos são adicionados apenas em juntas intermediárias (`i > 0`). O último ponto (extremidade final) NÃO recebe grepo. Na montagem real, todas as juntas que conectam coluna + viga precisam de grepo/cubo.

**Impacto:** Extremidade superior final da estrutura fica sem conector — impossível de montar.

**Solução:** Adicionar grepo também no último ponto do sketch se ele for uma extremidade de viga (não apenas juntas intermediárias).

**Prioridade:** ALTA

---

## B) ERROS MÉDIOS

### B1. Sketch não suporta diagonais

**Descrição:** `generateFromSketch()` usa rotação binária (`rotZ = isVertical ? 90 : 0`). Segmentos diagonais (ex: contraventamento inclinado) são tratados como horizontais ou verticais, perdendo o ângulo real.

```
Ponto A ───── Ponto B (dy > dx → tratado como vertical, rotZ=90)
                  ↖ ângulo ignorado
```

**Impacto:** Estruturas com diagonais (treliças, contraventamentos, coberturas inclinadas) não podem ser geradas pelo sketch.

**Solução:** Usar o ângulo real: `rotZ = Math.atan2(dy, dx) * (180 / Math.PI)`.

**Prioridade:** MÉDIA

### B2. Componente "Sapata" com tipo='sapata' não é usado em nenhuma geração

**Descrição:** O banco tem itens `tipo='sapata'` (id 10, 12, 13) mas a geração usa `tipo='Q30'` para o Pé de Galinha, filtrando por `q30s.find(p => Math.abs(p.comprimento - peLength) < 0.05)`. Os itens de sapata do catálogo NUNCA são usados pela engine de geração.

**Impacto:** Itens de sapata cadastrados no estoque são ignorados. O estoque mostrado nunca é descontado. O Pé de Galinha usa Q30s comuns, consumindo estoque de barra estrutural.

**Solução:** Criar tipo próprio `'pe_de_galinha'` no catálogo, ou usar as sapatas existentes com filtragem correta. Descontar do estoque de sapata, não de Q30.

**Prioridade:** MÉDIA

### B3. Rotação Y invertida no Three.js vs Fabric.js

**Descrição:** No 3D, `model.rotation.y = -comp.rotationY * (PI/180)`. O sinal negativo existe mas não tem correspondente no 2D — `isBeamAlongZ = Math.abs(comp.rotationY) > 45`. Um componente com `rotationY=90` aparece corretamente na 2D superior como barra Z, mas no 3D pode aparecer com orientação invertida.

**Impacto:** Discparidade visual entre vistas 2D e 3D para vigas laterais.

**Solução:** Documentar a convenção de rotação e garantir que ambos os renderers usem a mesma orientação. Testar visualmente.

**Prioridade:** MÉDIA

### B4. getTrussSegmentsForDistance duplica lógica do fallback greedy

**Descrição:** A função `getTrussSegmentsForDistance()` (linha 2916) replica o fallback greedy de `generateTrussLine()` sem o backtracking com pontuação de estoque. É uma versão simplificada que pode gerar combinações sub-ótimas ou diferentes das escolhidas pela engine principal.

**Impacto:** Discrepância entre segmentos sugeridos e os realmente usados.

**Solução:** Remover a função duplicada ou fazê-la chamar `generateTrussLine()` internamente.

**Prioridade:** MÉDIA

### B5. Sleeve com comprimento 0 não aparece nas vistas 2D

**Descrição:** O sleeve é criado com `length: 0`. No 2D frontal, ele é renderizado como quadrado de ~22px. Na 2D lateral e superior também. Mas na **lista quantitativa** ele aparece com `comprimento=0`, o que pode confundir.

**Impacto:** Sem impacto estrutural, mas o relatório quantitativo mostra sleeve com comprimento zero.

**Solução:** Manter `length=0` (sleeve não tem comprimento útil), mas no relatório exibir como "Unidade" ou "pç".

**Prioridade:** BAIXA

---

## C) MELHORIAS RECOMENDADAS

### C1. Suporte a ângulos livres no sketch

**Descrição:** O sketch atual trata segmentos como binário vertical/horizontal. Para suportar treliças com diagonais (coberturas, torres, grids inclinados), o ângulo real deve ser calculado e passado para `generateTrussLine()`.

**Implementação:**
```javascript
var angle = Math.atan2(dy, dx) * (180 / Math.PI);
// Normalizar: 0° = horizontal, 90° = vertical
var rotZ = ((angle % 180) + 180) % 180;
```

**Prioridade:** ALTA

### C2. Módulo de nós (Node System)

**Descrição:** Hoje o sistema não tem entidade "nó". Cubos e grepos são tratados como componentes independentes. Um sistema de nós permitiria:
- Verificar se um nó tem todas as conexões corretas
- Calcular carga em cada nó
- Garantir que cada nó tenha o tipo certo de conector

**Sugestão:**
```javascript
class Node {
    id, position: {x,y,z}, type: 'cubo'|'grepo'|'base',
    connections: [{ direction, componentId }]
}
```

**Prioridade:** ALTA

### C3. Contraventamento automático (Bracing)

**Descrição:** Estruturas reais de Box Truss exigem contraventamento diagonal para estabilidade lateral (wind bracing). O sistema atual não gera diagonais — apenas colunas e vigas ortogonais.

**Sugestão:** Adicionar opção "Contraventamento" que insere diagonais nos painéis da estrutura:
- X-bracing nas faces laterais (plano ZY)
- Diagonais no plano frontal quando necessário
- Configurável: sem / simples / duplo

**Prioridade:** ALTA

### C4. Pé de Galinha integrado ao catálogo

**Descrição:** O Pé de Galinha deveria ser um item de catálogo próprio (`tipo='pe_de_galinha'`), com comprimento (1m ou 2m), peso e estoque próprios. Hoje ele usa Q30s do estoque comum.

**Solução:** Adicionar `tipo='pe_de_galinha'` ao catálogo e filtrar por ele na geração.

**Prioridade:** MÉDIA

### C5. Validação de conexões

**Descrição:** Não há verificação se as conexões entre componentes são válidas. Por exemplo, um cubo e uma Q30 conectados em ângulos incompatíveis não são detectados.

**Sugestão:** Adicionar validação pós-geração que verifica:
- Cada junta tem conector suficiente (cubo/grepo)
- Cada Q30 conecta em faces válidas do conector
- Não há sobreposição de volumes

**Prioridade:** MÉDIA

### C6. Suporte a Q25, Q50 e outros perfis

**Descrição:** O sistema só modela Q30 (30×30cm). Mercado usa também Q25 (25×25cm, leve) e Q50 (50×50cm, pesado). A engine de geração usa `q30Thickness = 0.30 * scale` hardcoded.

**Solução:** Parametrizar a espessura por tipo. O `createTrussSection` no Three.js também usa `size = 0.3` fixo.

**Prioridade:** BAIXA (futuro)

### C7. Labels 3D sobrepostas

**Descrição:** No 3D, cada componente recebe um sprite de texto flutuante 0.5m acima. Em juntas com múltiplos componentes (cubo + grepo + sleeves), os labels se sobrepõem.

**Solução:** Agrupar labels por posição, exibindo apenas um label consolidado por nó.

**Prioridade:** BAIXA

---

## D) MELHORIAS FUTURAS

### D1. Engine de estruturas completas em 3D

Suportar geração de:
- Torres treliçadas (estrutura vertical 3D fechada)
- Coberturas inclinadas (roof trusses)
- Grids 3D (estrutura planar suspensa)
- Pórticos multiplanos (múltiplos pórticos conectados)

### D2. Cálculo estrutural

Integrar análise de:
- Carga máxima por nó
- Momento fletor nas vigas
- Deflexão máxima
- Vento (carga lateral)
- Fator de segurança

### D3. Integração BIM / IFC

Exportar modelo no formato IFC (Industry Foundation Classes) para uso em Revit, Tekla, etc.

### D4. Otimização de estoque multi-projeto

Otimizar uso de Q30s entre múltiplos projetos simultâneos (estoque compartilhado).

### D5. Realidade Aumentada

Sobrepor estrutura gerada no ambiente real via câmera do dispositivo.

---

## E) MODELO IDEAL DE ARQUITETURA

```
┌─────────────────────────────────────────────────────────┐
│                    Frontend (Alpine + PHP)               │
├─────────────────────────────────────────────────────────┤
│  UI Layer     │  Toolbar  │  Canvas 2D  │  3D Viewer    │
├─────────────────────────────────────────────────────────┤
│  Sketch Layer │  click → pixelToMeters → sketchPoints   │
├─────────────────────────────────────────────────────────┤
│  Generation   │  generateAutoLayout / generateFromSketch│
│  Engine       │  generateTrussLine / addSideBeams      │
├─────────────────────────────────────────────────────────┤
│  Node System  │  Node {pos, type, connections[]}        │
├─────────────────────────────────────────────────────────┤
│  Stock        │  backtracking → scoring → virtualStock  │
│  Optimizer    │  stock-aware combination selection      │
├─────────────────────────────────────────────────────────┤
│  BOM          │  calculateQuantitative → export PDF/XLS │
├─────────────────────────────────────────────────────────┤
│  Project DB   │  SQLite + API REST (save/load/delete)   │
└─────────────────────────────────────────────────────────┘
```

### Entidades necessárias no modelo de dados

```
Produto:    id, codigo, nome, tipo, comprimento, peso, estoque, ativo
Projeto:    id, name, width, height, length, scale, components (JSON)
Nó:         id, projeto_id, position{x,y,z}, tipo_conector
Componente: id, projeto_id, no_id, produto_id, rotation{x,y,z}
Estoque:    id, produto_id, saldo, reservado, projeto_id (opcional)
```

### Rotas de API sugeridas

```
GET    /api/pecas          → lista todos produtos ativos
POST   /api/pecas          → criar/atualizar produto
DELETE /api/pecas/:id      → desativar produto
GET    /api/projects       → lista projetos salvos
POST   /api/projects       → salvar projeto
DELETE /api/projects/:id   → excluir projeto
GET    /api/nos/:projectId → lista nós do projeto
POST   /api/generate       → engine de geração (server-side)
```

---

## F) ROADMAP DE EVOLUÇÃO

### Fase 1 — Correções Críticas (1-2 semanas)

| Item | Descrição | Esforço |
|------|-----------|---------|
| A1 | Encurtar coluna na base | 2h |
| A2 | UID sequencial (evitar colisão) | 1h |
| A3 | Sleeves em vigas laterais (Z) | 3h |
| A4 | Grepo no último ponto do sketch | 1h |
| C1 | Ângulos livres no sketch (diagonais) | 6h |
| C2 | Sistema de nós (Node System) | 16h |

### Fase 2 — Melhorias Estruturais (2-3 semanas)

| Item | Descrição | Esforço |
|------|-----------|---------|
| B1 | Validação completa de ângulos | 4h |
| B2 | Sapata como tipo próprio no catálogo | 4h |
| C3 | Contraventamento automático | 16h |
| C4 | Pé de Galinha integrado ao catálogo | 4h |
| C5 | Validação de conexões | 8h |

### Fase 3 — Qualidade (1-2 semanas)

| Item | Descrição | Esforço |
|------|-----------|---------|
| B3 | Consistência de rotação 2D/3D | 4h |
| B4 | Remover duplicação getTrussSegmentsForDistance | 2h |
| C7 | Labels 3D não sobrepostos | 4h |

### Fase 4 — Futuro (indefinido)

| Item | Descrição |
|------|-----------|
| D1 | Engine de estruturas 3D completas |
| D2 | Cálculo estrutural |
| C6 | Suporte a Q25 / Q50 |

---

## RESUMO DE PRIORIDADES

| Prioridade | Qtde | Itens |
|------------|------|-------|
| **ALTA** | 6 | A1, A2, A3, A4, C1, C2 |
| **MÉDIA** | 7 | B1, B2, B3, B4, C3, C4, C5 |
| **BAIXA** | 4 | B5, C6, C7, D1-D5 |

---

### C8. Parafusos e Pinos ausentes do sistema

**Descrição:** Toda montagem real de Box Truss utiliza parafusos e pinos de travamento (spigots, bolts, cotter pins) em CADA conexão entre componentes. O sistema atual não modela, conta ou desconta do estoque nenhum elemento de fixação.

**Componentes de fixação típicos por conexão:**
- **Cubo → Q30:** 1 spigot cônico + 1 pino de travamento por face conectada
- **Sleeve → Q30:** 2 parafusos Allen (um em cada lado da emenda)
- **Grepo → Q30:** 1 spigot + 1 pino por face
- **Sapata → Cubo:** 1 pino de fixação

**Impacto:** A lista de materiais pode subestimar em 30-50 peças de fixação por estrutura. O orçamento fica incompleto e a montagem não tem os insumos necessários.

**Solução (curto prazo):** Adicionar estimativa automática:
```javascript
const estimarFixacoes = (components3D) => {
    let parafusos = 0, pinos = 0;
    components3D.forEach(c => {
        if (c.tipo === 'sleeve') parafusos += 2;
        if (c.tipo === 'cubo' || c.tipo === 'grepo') pinos += 4; // estimativa por conector
    });
    return { parafusos, pinos };
};
```

**Prioridade:** ALTA

### C9. Representação 2D não mostra cruzetas/travessas internas

**Descrição:** O modelo 3D (`createTrussSection`) representa corretamente os 4 tubos de canto (32mm), travessas horizontais a cada 40cm e diagonais zig-zag. Porém, na **renderização 2D (Fabric.js)**, a Q30 é desenhada como um retângulo sólido — sem indicar a estrutura interna de cruzetas e travessas.

**Impacto:** A vista 2D dá a impressão de que a Q30 é uma barra maciça, quando na verdade é uma treliça aberta com tubos e diagonais. Técnicos de montagem podem se confundir.

**Solução:** Adicionar padrão de linhas diagonais no preenchimento do retângulo Q30 no 2D:
```javascript
// Dentro de createFabricQ30Object, adicionar linhas diagonais
const pattern = new fabric.Pattern({
    source: criarCanvasPadraoTrelica(),
    repeat: 'repeat'
});
rect.set('fill', pattern); // em vez de fillColor sólido
```

**Prioridade:** MÉDIA

---

## ANEXO: Checklist de Verificação por Componente

| Componente | Função Real | Implementado? | Correto? | Observação |
|------------|-------------|---------------|----------|------------|
| **Q30** | Perfil estrutural 30×30cm, comprimentos 0.5-5m | ✅ | ✅ | Cores por comprimento, modelo 3D com diagonais |
| **Cubo** | Conector cúbico 30cm nas juntas | ✅ | ⚠️ | Sobreposição com coluna na base (A1) |
| **Grepo** | Conector rosa/vermelho nos cantos | ✅ | ⚠️ | Ausente no último ponto do sketch (A4) |
| **Sleeve** | Luva de emenda entre Q30s no mesmo eixo | ✅ | ✅ | Sem sleeves nas vigas laterais (A3) |
| **Sapata** | Pé de Galinha — base com Q30 deitada | ✅ | ⚠️ | Usa Q30 do estoque em vez de item próprio (B2) |
| **Q25** | Perfil 25×25cm (leve) | ❌ | ❌ | Não implementado |
| **Q50** | Perfil 50×50cm (pesado) | ❌ | ❌ | Não implementado |
| **Pé de Galinha** | Base de apoio com Q30 deitada ao longo de Z | ✅ | ⚠️ | Deveria ser item de catálogo próprio |
| **Base Apoio** | Chapa de base para piso | ❌ | ❌ | Não implementado |
| **Motor** | Motor de movimentação | ❌ | ❌ | Não implementado |
| **Talha** | Talha elétrica | ❌ | ❌ | Não implementado |
| **Pau de Carga** | Barra de carga | ❌ | ❌ | Não implementado |
| **Corner Block** | Bloco de canto para coberturas | ❌ | ❌ | Não implementado |
| **Adaptador** | Adaptador entre perfis (Q30→Q50) | ❌ | ❌ | Não implementado |
| **Torre** | Estrutura vertical completa | ❌ | ❌ | Não implementado — seria composição |

---

*Relatório gerado automaticamente em 2026-06-10. A análise considerou todo o código-fonte disponível em `app/views/home/index.php`, `app/views/pecas/index.php`, `app/views/layouts/main.php`, `database/database.sqlite` e arquivos CSS/JS de suporte.*
