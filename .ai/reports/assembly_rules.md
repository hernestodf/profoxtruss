# ProFoxTruss — Documentação de Montagem Box Truss Q30/P30

## 1. Sistema de Coordenadas

```
       Y (altura)
       ▲
       │
       │   ┌─── Z (profundidade, para frente)
       │  ╱
       │ ╱
       ───────→ X (comprimento, horizontal)
```

- **X**: horizontal (comprimento), centro em 0, varia de `-length/2` a `+length/2`
- **Y**: vertical (altura), `baseY = 0` no chão, `topY = height` no topo
- **Z**: profundidade, `0` na frente, `-width` atrás

**Canvas 2D**: 750×500px, centro `cx=375`, `cy=250`  
**FloorY (pixels)**: `cy + (height * scale) / 2 - 40`  
**Escala padrão**: 1m = 50px

---

## 2. Componentes

### 2.1 Treliça (Módulo Q30 / P30)

| Perfil | Bitola | Tubo Canto | Aplicação |
|--------|--------|------------|-----------|
| Q30 | 30×30cm | 32mm (1¼") | Uso geral, eventos de médio porte |
| P30 | 32×32cm | 50,8mm (2") | Linha pesada, alta carga, rigging industrial |

**Comprimentos padronizados:** 0.5m, 1.0m, 1.5m, 2.0m, 3.0m, 4.0m, 5.0m

**Estrutura interna (modelo 3D):**
- 4 tubos de canto (32mm Q30 / 50,8mm P30)
- Travessas horizontais a cada ~40cm (tubo 14mm)
- Diagonais zig-zag (tubo 14mm)
- Placas de extremidade translúcidas

### 2.2 Cubo (Junção)

| Tipo | Dimensões | Peso | Faces | Material |
|------|-----------|------|-------|----------|
| Cubo Q30 | 300×300×300mm | ~3kg | 6 | Alumínio |
| Cubo 5 Faces P30 | 320×320×300mm | 11,00 kg | 5 | Alumínio 6351-T6 |

**Função real:** Conector universal para junções L (canto 90°) e T (três treliças convergindo).  
**Posição:** Centro da junta. Cada face recebe uma Q30 encaixada e fixada com spigot + pino.  
**Volume físico:** 0.30m × 0.30m × 0.30m — NUNCA pode ser penetrado por Q30.

### 2.3 Pé de Galinha (Sapata)

| Tipo | Total | Cada lado | Q30 utilizada |
|------|-------|-----------|---------------|
| pg1 | 1,30m | 0.5m + cubo + 0.5m | 2 × Q30 0.5m |
| pg2 | 2,30m | 1.0m + cubo + 1.0m | 2 × Q30 1.0m |

**Forma:** T no chão:
```
          Coluna
             │
   ←──[cubo]──→  2 Q30s, cada uma saindo da face do cubo
```

**Cada Q30:** começa EXATAMENTE na face externa do cubo (`z = col.z + 0.15 + peLength/2` para frente, `z = col.z - 0.15 - peLength/2` para trás).  
**Stock:** decrementa 2 unidades (uma para cada lado).

### 2.4 Grepo (Conector de Grade)

**Função real:** Conector para hanging points, ancoragem em lajes existentes, conexão de grids de iluminação, união entre estruturas separadas.  
**No sistema:** Adicionado MANUALMENTE via catálogo (drag-and-drop). Não é gerado automaticamente.  
**Montagem:** Não usado em pórticos padrão — usado para conectar estruturas entre si.

### 2.5 Sleeve (Luva de Emenda)

**Função real:** Emenda linear entre duas Q30 no mesmo eixo. Acompanha 2 parafusos Allen.  
**No sistema:** Adicionado MANUALMENTE via catálogo. Não é gerado automaticamente.  
**Volume físico:** 320×320×? (ligeiramente maior que Q30 para encaixe).

### 2.6 Sleeve 4 Faces P30

**Função real:** Módulo estrutural de içamento — monta a cobertura no chão, prende lona, acopla talhas/motores, e eleva.  
**Característica exclusiva:** PERCORRE DENTRO DA ESTRUTURA — o Sleeve 4 Faces pode ser posicionado em QUALQUER ponto ao longo de uma Q30, deslizando/rolando internamente para posicionar talhas e motores exatamente onde necessário.  
**Não é um conector fixo** — é um componente MÓVEL que corre dentro do perfil da treliça.  
**Dimensões:** 500×520×520mm  
**Peso:** 22,85 kg  
**Material:** Alumínio 6351-T6  
**Solda:** TIG Stack of Dimes  
**Catálogo:** tipo `sleeve_4faces`, badge **MÓVEL** na interface.**

### 2.7 Grapple

**Função real:** Peça curinga em aço ASTM A36, perfil U, furos oblongos 17×38mm, chassi 42×30×300mm.  
**Aplicação:** Unir estruturas sem necessidade de cubo, pendurar equipamentos diversos.  
**Tratamento:** Zincagem eletrolítica anti-oxidação.  
**Não modelado atualmente.**

---

## 3. Regras de Montagem

### 3.1 Cubos e Conectores são SÓLIDOS FÍSICOS

Cubos e conectores **NÃO são pontos matemáticos**. Todo cubo ocupa 0.30m × 0.30m × 0.30m de volume físico real.

**Regra fundamental:** Uma Q30 nunca pode atravessar ou ocupar o mesmo volume de um cubo. As extremidades das Q30 devem encostar **exatamente na face externa** do cubo.

```
Errado:      Q30 → [●cubo] ← Q30    (Q30s penetram o cubo)
Correto:     Q30 →|cubo|← Q30       (Q30s encostam nas faces)
```

### 3.2 Junções L e T → CUBO

| Junção | Geometria | Conector |
|--------|-----------|----------|
| Canto 90° (L) | Q30 vertical + Q30 horizontal | Cubo |
| Três vias (T) | 2 horizontais + 1 vertical | Cubo |
| Quatro vias (+) | 2 horizontais + 1 vertical + 1 profundidade | Cubo |

### 3.3 Conexão Direta (mesmo eixo) → SEM CONECTOR

Quando duas Q30s se encontram no mesmo eixo (emenda linear), a conexão é direta. Não usa cubo, não usa grepo. O sleeve é adicionado manualmente se necessário.

### 3.4 Junções entre Estruturas → GREPO (manual)

Quando uma estrutura é ligada a outra (não é L ou T), usa grepo — mas é adição manual via catálogo, não geração automática.

### 3.5 Dimensão Física Total

```
largura_total  = soma_das_q30_horizontais + soma_dos_cubos_intermediarios
altura_total   = soma_das_q30_verticais + soma_dos_cubos_intermediarios
profundidade_total = soma_das_q30_profundidade + soma_dos_cubos_intermediarios
```

Exemplo: Q30 1m + Cubo 30cm + Q30 1m = 2,30m (não 2,00m)

As dimensões exibidas nas vistas 2D e 3D são: **configurada + 0.30m** (cubo de cada lado).

---

## 4. Motor Geométrico

### 4.1 Geração Automática (`generateAutoLayout`)

**Estrutura gerada:**
```
1. Colunas frontais:    startX → topY-cuboHalf (Q30, rotZ=90)
                         endX  → topY-cuboHalf (Q30, rotZ=90)
2. Viga frontal:        startX+cuboHalf → endX-cuboHalf (Q30, rotZ=0)
3. Cubos frontais:      (startX, topY, 0) + (endX, topY, 0)
4. Pé de Galinha:       cubo + 2 Q30s (frente/trás) em cada coluna
5. Frame traseiro:      (se width > 0.5) repetir 1-4 em z=-width
6. Vigas laterais:      addSideConnectingBeams nos topos (Q30, rotY=90)
```

**cuboHalf = 0.15m:** Cada Q30 é encurtada 0.15m em cada extremidade que encontra um cubo. A Q30 termina exatamente na face externa do cubo.

**columnBaseY:** Quando sapata ativa, coluna começa em `baseY + cuboHalf` para não penetrar o cubo da sapata.

### 4.2 Geração por Sketch (`generateFromSketch`)

**Fluxo:**
1. Usuário clica pontos no canvas (modo sketch ativo)
2. Cada par consecutivo forma um segmento
3. Ângulo real calculado: `rotZ = Math.atan2(dy, dx) * (180 / PI)` (não binário)
4. Segmento encurtado `cuboHalf` em cada extremidade
5. Q30s preenchem o segmento (algoritmo stock-aware)
6. Cubos nas juntas intermediárias
7. Se width > 0.5: frame traseiro espelhado + vigas laterais
8. Pé de Galinha no primeiro e último ponto

### 4.3 Algoritmo de Preenchimento (`generateTrussLine`)

```
1. Distância total entre (x1,y1,z1) e (x2,y2,z2)
2. BACKTRACKING: encontra TODAS as combinações de Q30 do catálogo
   que somam exatamente a distância (tolerância 0.01m)
3. PONTUAÇÃO de cada combinação:
   score = -outOfStockPenalty - comb.length
   outOfStockPenalty = (needed - available) * 100 por item faltante
4. MELHOR combinação = menor nº de segmentos respeitando estoque
5. Se nenhuma combinação exata: FALLBACK GREEDY
   (maior Q30 que cabe de cada vez)
6. Posiciona cada Q30 centrada ao longo da direção, decrementa stock
```

### 4.4 Geração por Linhas (`getTrussSegmentsForDistance`)

Função auxiliar para pré-visualização de segmentos. Usa fallback greedy (sem backtracking).

---

## 5. Renderização

### 5.1 Vista 2D (Fabric.js)

| Vista | Eixos | Fórmula coord. |
|-------|-------|----------------|
| Frontal | X × Y | `left = cx + comp.x * scale`, `top = floorY - comp.y * scale` |
| Superior | X × Z | `left = cx + comp.x * scale`, `top = cy + (comp.z + depth/2) * scale` |
| Lateral | Z × Y | `left = cx + (comp.z + depth/2) * scale`, `top = floorY - comp.y * scale` |

**Dimensões físicas totais:** linhas de cota estendidas em `cuboPx/2` (metade cubo) em cada extremidade, label mostra `(dimensão + 0.30)m`.

**Cores por comprimento Q30:**

| Comprimento | Contorno | Preenchimento |
|-------------|----------|---------------|
| 3.0m | Sky blue `#38bdf8` | `rgba(56,189,248,0.18)` |
| 2.0m | Emerald `#10b981` | `rgba(16,185,129,0.18)` |
| 1.5m | Cyan `#06b6d4` | `rgba(6,182,212,0.18)` |
| 1.0m | Amber `#fbbf24` | `rgba(251,191,36,0.18)` |
| 0.5m | Pink `#ec4899` | `rgba(236,72,153,0.18)` |
| outro | Indigo `#6366f1` | `rgba(99,102,241,0.18)` |

**Conectores:**
- Cubo: laranja `#f97316`
- Grepo: rosa `#f43f5e`
- Sleeve: roxo `#a855f7`

### 5.2 Vista 3D (Three.js)

- Câmera: PerspectiveCamera(40, 750/500, 0.1, 1000) em `(0, 3.5, 9)`
- OrbitControls com damping
- GridHelper(30, 30) em y=0.001
- Iluminação: Hemisphere + 2 Directional + PointLight roxo
- Fog atmosférico: `FogExp2('#070a13', 0.03)`

**Modelo Q30:** Grupo Three.js com:
- 4 tubos de canto 32mm (CylinderGeometry, 8 segmentos)
- Travessas horizontais 14mm a cada ~40cm
- Diagonais zig-zag 14mm
- Placas de extremidade translúcidas
- Pré-rotacionado -90° em Z (deita ao longo de X)

**Rotações (convenção 2D → 3D):**
```
model.rotation.x = comp.rotationX * PI/180
model.rotation.y = -comp.rotationY * PI/180  
model.rotation.z += -comp.rotationZ * PI/180
```
Nota: Y e Z invertidos vs. convenção 2D.

---

## 6. Lista de Materiais (BOM)

### 6.1 Cálculo (`calculateQuantitative`)

Agrupa `components3D` por `codigo`:
```
quantitativo[i] = {
    codigo, nome, tipo, peso,
    projeto:    nº de vezes que aparece na estrutura,
    estoque:    saldo no catálogo,
    diferenca:  max(0, projeto - estoque)
}
```

### 6.2 Estimativa de Fixadores

| Componente | Por unidade |
|------------|-------------|
| Cubo | 4 pinos + spigots |
| Grepo | 4 pinos + spigots |
| Sleeve | 2 parafusos Allen |

Incluído no BOM como itens `PINO` e `PARF`.

---

## 7. Estoque e Otimização

### 7.1 Stock-Aware Algorithm

1. O algoritmo de backtracking testa TODAS as combinações possíveis de Q30 do catálogo
2. Cada combinação recebe uma pontuação que penaliza itens sem estoque:
   ```
   score = -outOfStockPenalty - comb.length
   outOfStockPenalty = (needed - available) * 100
   ```
3. A combinação com MAIOR pontuação é selecionada
4. Se nenhuma combinação exata existe, usa fallback greedy (maior Q30 disponível)

### 7.2 Virtual Stock

`virtualStock` é um mapa `{ [peca.id]: saldo }` que é decrementado a cada Q30 utilizada:
- Colunas e vigas: `generateTrussLine` decrementa
- Vigas laterais: `addSideConnectingBeams` decrementa
- Pé de Galinha: decrementa 2 por coluna (frente + trás)

### 7.3 Catálogo Mínimo para Geração

| Função | Tipo | Obrigatório |
|--------|------|-------------|
| Q30 estruturais | `'Q30'` | ✅ |
| Cubo | `'cubo'` | ✅ |
| Sleeve | `'sleeve'` | ✅ |
| Grepo | `'grepo'` | ❌ (manual) |

---

## 8. Diagrama de Nós e Conexões

```
                    ┌──────────────────────┐
                    │     Viga Superior     │  (Q30, rotZ=0)
                    │   [sX+0.15]──[eX-0.15]
                    │         ▲              ▲
                    │    cubo │              │ cubo
                    │         │              │
  Z=0 (frente)      │   Coluna│           Coluna│  (Q30, rotZ=90)
                    │     │   │              │  │
                    │     │   │              │  │
                    │  ┌──┴───┐          ┌──┴───┐
                    │  │ cubo │          │ cubo │   ← Pé de Galinha
                    │  │┼┼┼┼┼│          │┼┼┼┼┼│
                    │  └─────┘          └─────┘
                    │  ← Q30 → ← Q30 →  ← Q30 → ← Q30 →
                    │  (trás)(frente)   (trás)(frente)
                    │
                    │   Dimensão física total = length + 0.30
                    │
    Z=-width        │   (mesma estrutura, espelhada em Z)
    (atrás)         └──────────────────────────────────────
                            │              │
                     sideBeams(x)    sideBeams(x)
                     (Q30, rotY=90)  (Q30, rotY=90)
```

---

## 9. Histórico de Correções

| Data | Item | Descrição |
|------|------|-----------|
| 2026-06-10 | A1 | Coluna encurtada na base (`columnBaseY`) |
| 2026-06-10 | A2 | UID sequencial (`_uid()`), sem colisão de Date.now() |
| 2026-06-10 | A3 | Sleeves removidos da geração automática |
| 2026-06-10 | A4 | Grepo → Cubo nos cantos superiores |
| 2026-06-10 | A4 | Grepo removido da geração automática (manual via catálogo) |
| 2026-06-10 | C1 | Ângulos livres no sketch (`Math.atan2`) |
| 2026-06-10 | C8 | Fixadores no BOM (pinos + parafusos Allen) |
| 2026-06-10 | — | Dimensões físicas totais (+0.30m cubos) |
| 2026-06-10 | — | Pé de Galinha = duas Q30s (frente + trás) sem overlap |
| 2026-06-10 | — | Cubo = sólido físico 0.30m, Q30 termina na face |
