# Plano: Modo Sketch — Desenho de Estrutura por Pontos

## Data: 2026-06-10

---

## Objetivo

Criar um modo de desenho visual ("Sketch") onde o usuário clica pontos na tela, e o sistema gera automaticamente a estrutura de Q30 entre esses pontos, mostrando o pórtico 3D e o estoque.

---

## Arquitetura

O sistema já possui toda a engine necessária:

| Componente | Local | Função |
|---|---|---|
| `buildTrussLine3D(x1,y1,z1, x2,y2,z2, rotX,rotY,rotZ)` | `home/index.php:1076` | Preenche segmento de reta com barras Q30 + sleeves |
| `this.components3D` | data property | Array de todos os componentes |
| `syncCanvasFrom3D()` | `home/index.php:1984` | Renderiza 3D → 2D (fabric.js) |
| `init3DView()` | `home/index.php:1553` | Renderiza 3D (Three.js) |
| `calculateQuantitative()` | `home/index.php:1271` | Estoque vs projeto |
| `addSideConnectingBeams()` | `home/index.php:814` | Conexões laterais em Z |

O modo Sketch reutiliza essas funções — cada par de pontos vira uma chamada a `buildTrussLine3D`.

---

## Fases

### Fase 1: Modo Sketch na UI

**Arquivo:** `home/index.php`

#### 1a. Estado e botão

Novas props: `sketchMode: false`, `sketchPoints: []`, `sketchFabricObjects: []`

Botão na toolbar (após Snap):
```html
<button @click="toggleSketchMode()"
        :class="sketchMode ? 'bg-neon-amber/20 border-neon-amber text-neon-amber' : 'bg-bg-surface border-bg-border text-text-3'"
        class="flex items-center gap-1.5 px-3 py-1.5 rounded-xl border text-xs font-bold transition-all">
    ✏️ Sketch
</button>
```

#### 1b. toggleSketchMode()

Ativar:
- `canvas.selection = false`
- `canvas.on('mouse:down', this.onSketchClick)`
- Instruções visuais no canvas

Desativar:
- `canvas.selection = true`
- Remover listener
- Remover objetos de sketch

#### 1c. onSketchClick(e)

Converter coordenadas do clique para metros (usando `canvasScale` e viewMode). Adicionar ponto a `sketchPoints[]`. Chamar `renderSketch()`.

#### 1d. renderSketch()

Renderizar no canvas:
- `fabric.Circle` para cada ponto
- `fabric.Line` (dashed, neon-cyan) entre pontos
- `fabric.Text` com distância em metros

Manter objetos em `sketchFabricObjects[]` para limpeza.

#### 1e. Botões de ação

```html
<div x-show="sketchMode" class="flex gap-2 mt-2">
    <button @click="undoSketchPoint()">↩ Desfazer</button>
    <button @click="clearSketch()">🗑 Limpar</button>
    <button @click="generateFromSketch()">🔧 Gerar Estrutura</button>
</div>
```

### Fase 2: Refatorar buildTrussLine3D → generateTrussLine()

Extrair a função interna de `generateAutoLayout()` para método independente:

```javascript
generateTrussLine(x1, y1, z1, x2, y2, z2, rotX, rotY, rotZ, q30s, virtualStock, sleevePeca) {
    // algoritmo de combinação + sleeves (cópia do atual)
}
```

Em `generateAutoLayout()`, `buildTrussLine3D` passa a chamar `this.generateTrussLine(...)`.

### Fase 3: generateFromSketch()

```javascript
generateFromSketch() {
    if (sketchPoints.length < 2) return;
    components3D = [];

    const q30s = catalog.filter(p => p.tipo === 'Q30').sort(...);
    const grepoPeca = catalog.find(p => p.tipo === 'grepo');
    const sleevePeca = catalog.find(p => p.tipo === 'sleeve');
    if (!q30s.length || !grepoPeca || !sleevePeca) return alert("Catálogo incompleto!");

    const virtualStock = {};
    q30s.forEach(p => virtualStock[p.id] = p.estoque);
    const depth = dimensions.width;

    for (let i = 0; i < sketchPoints.length - 1; i++) {
        const p1 = sketchPoints[i], p2 = sketchPoints[i+1];
        const dx = p2.x - p1.x, dy = p2.y - p1.y;
        const isVertical = Math.abs(dy) > Math.abs(dx);
        const rotZ = isVertical ? 90 : 0;

        generateTrussLine(p1.x, p1.y, 0, p2.x, p2.y, 0, 0, 0, rotZ, q30s, virtualStock, sleevePeca);

        if (i > 0) {
            components3D.push({ tipo:'grepo', x:p1.x, y:p1.y, z:0, ... });
        }
    }

    if (depth > 0.5) {
        // Espelhar para Z=-depth
        for (let i = 0; i < sketchPoints.length - 1; i++) {
            const p1 = sketchPoints[i], p2 = sketchPoints[i+1];
            const dx = p2.x - p1.x, dy = p2.y - p1.y;
            const rotZ = Math.abs(dy) > Math.abs(dx) ? 90 : 0;
            generateTrussLine(p1.x, p1.y, -depth, p2.x, p2.y, -depth, 0, 0, rotZ, q30s, virtualStock, sleevePeca);
            if (i > 0) components3D.push({ tipo:'grepo', x:p1.x, y:p1.y, z:-depth, ... });
        }
        sketchPoints.forEach(p => addSideConnectingBeams(p.x, p.y, virtualStock));
    }

    sketchMode = false;
    clearSketch();
    syncCanvasFrom3D();
    calculateQuantitative();
}
```

### Fase 4: Edição de Distâncias

- Labels de distância como `fabric.IText` ou input HTML
- Ao alterar valor, recalcular posição do ponto final mantendo ângulo
- Re-renderizar sketch

### Fase 5: Limpeza

- Sair do sketchMode ao gerar ou ao trocar de view
- Garantir que `generateAutoLayout()` funcione após sketch
- Adicionar instruções no canvas

---

## Riscos

1. `buildTrussLine3D` é `const` dentro de `generateAutoLayout()` — refatorar para método
2. Coordenadas em metros, converter pixel↔metro com `canvasScale`
3. `virtualStock` isolado por geração (não conflita)
4. Grepo apenas em pontos intermediários
5. Desabilitar seleção/drag durante sketch

---

## Arquivos Afetados

Apenas `app/views/home/index.php` — frontend JavaScript. Nenhuma mudança no backend.

---

## Esforço

| Fase | Descrição | Esforço |
|---|---|---|
| 1 | UI + renderização sketch | 1h |
| 2 | Refatorar generateTrussLine | 30min |
| 3 | generateFromSketch + profundidade | 1.5h |
| 4 | Edição de distâncias | 30min |
| 5 | Limpeza | 30min |
| **Total** | | **4h** |
