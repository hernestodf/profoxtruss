# Análise: Cubo, Conexões e Pé de Galinha

## Data: 2026-06-10

---

## Problemas Identificados

### Problema 1: `isConector()` trata Q30 de 0,30m como conector (BUG CRÍTICO)

**Local**: `app/views/home/index.php:1994` e `:1802`

```javascript
// ATUAL (ERRADO):
const isConector = (c) => c.tipo === 'cubo' || (c.tipo === 'Q30' && Math.abs(parseFloat(c.length) - 0.30) < 0.01);
```

Esta função classifica **qualquer barra Q30 de 0,30m** como cubo conector. Causa:
- **2D** (linha 2152, 2308, 2465): Q30 de 0,30m renderiza como quadrado laranja
- **3D** (linha 1802): Q30 de 0,30m renderiza como cubo laranja maciço
- **Labels 3D** (linha 1831): aparece "CON" em vez de "0.3m"

**Solução**: `isConector` verificar APENAS `c.tipo === 'cubo'`.

---

### Problema 2: Sobreposição visual Cubo × Q30 nas vistas 2D

**Local**: `syncCanvasFrom3D()` — renderização frontal (2149), superior (2307), lateral (2464)

**Causa**: Cubo (0,30m) e espessura Q30 (0,30m) têm mesmo tamanho. Retângulos encostados parecem bloco único.

**Solução**: strokeWidth=4 + cor laranja mais intensa (`#ff8c00`) no cubo para destacá-lo.

---

### Problema 3: Pé de Galinha — estrutura e sapata padrão

**Local**: `generateAutoLayout()` (linhas 1043-1262)

**Atualmente**: Usa itens tipo 'sapata' do catálogo (SAPATA/SAPATA1/SAPATA2), renderiza como placa verde.

**O usuário diz**:
- Pé de Galinha (pg1=1m, pg2=2m) = **CUBO + 2 Q30 deitadas** (X e Z, formando T) na base
- A coluna SOBE do cubo
- Sapata padrão **não é usada** — remover

**Solução**:
- pg1/pg2: criar CUBO + 2 Q30 deitadas (1m ou 2m ao longo de X e Z) na base de cada coluna
- Coluna começa do `baseY` (sem `sapataOffset`, que era 0.03)
- Remover `sapataType === 'padrao'` do auto-layout e do seletor UI
- Remover `createSapataModel()` e renderização de sapata em 2D/3D

---

### Problema 4: Sleeves não adicionados entre segmentos Q30

**Local**: `buildTrussLine3D()` (linhas 1084-1166)

**Problema**: Múltiplos segmentos Q30 na mesma linha ficam sem sleeve entre eles.

**Solução**: Inserir sleeve na junção entre cada par de segmentos adjacentes.

---

## Plano de Correção (4 fases)

| Fase | O que | Local | Esforço |
|------|-------|-------|---------|
| 1 | Corrigir `isConector()` | 1994, 1802, 1831 | 5 min |
| 2 | Destacar cubo em 2D (stroke) | 2149-2163, 2307-2325, 2464-2485 | 15 min |
| 3 | Refatorar `generateAutoLayout()`: Pé de Galinha como CUBO + 2 Q30 deitadas, remover sapata padrão, sleeves | 1043-1262 | 2h |
| 4 | Remover sapata do catálogo UI e renderizações 2D/3D | `home/index.php` + `pecas/index.php` | 30 min |

**Tempo total**: ~3h

---

## Detalhamento das Alterações

### Fase 1: Corrigir `isConector()` (linha 1994)

```diff
- const isConector = (c) => c.tipo === 'cubo' || (c.tipo === 'Q30' && Math.abs(parseFloat(c.length) - 0.30) < 0.01);
+ const isConector = (c) => c.tipo === 'cubo';
```

Também em `init3DView()` (linha 1802):
```diff
- if (comp.tipo === 'cubo' || (comp.tipo === 'Q30' && Math.abs(comp.length - 0.30) < 0.01)) {
+ if (comp.tipo === 'cubo') {
```

### Fase 2: Destacar cubo nas vistas 2D

3 blocos de renderização do cubo no `syncCanvasFrom3D()`:
- Frontal: linhas 2149-2163
- Superior: linhas 2307-2325
- Lateral: linhas 2464-2485

```diff
- strokeWidth: 3,
+ strokeWidth: 4,
+ stroke: '#ff8c00',
```

### Fase 3: Refatorar `generateAutoLayout()`

#### 3a. Estrutura do Pé de Galinha (CUBO + 2 Q30 deitadas)

Substituir o bloco de sapatas inteiro (linhas 1191-1254) por:

```javascript
// Gerar Pé de Galinha (cubo + Q30 deitadas) ou nada
if (this.sapataType === 'pg1' || this.sapataType === 'pg2') {
    const peLength = this.sapataType === 'pg1' ? 1.0 : 2.0;
    const pePeca = q30s.find(p => Math.abs(p.comprimento - peLength) < 0.05);
    const peCubo = this.catalog.find(p => p.tipo === 'cubo');
    if (pePeca && peCubo) {
        const colunas = [
            { x: startX, z: 0 },
            { x: endX, z: 0 }
        ];
        if (width > 0.5) {
            colunas.push({ x: startX, z: -width });
            colunas.push({ x: endX, z: -width });
        }
        colunas.forEach(col => {
            // CUBO na base
            this.components3D.push({
                uid: 'comp_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9),
                catalogId: peCubo.id, codigo: peCubo.codigo, tipo: peCubo.tipo,
                length: 0.30, nome: peCubo.nome, peso: peCubo.peso,
                x: col.x, y: baseY, z: col.z,
                rotationX: 0, rotationY: 0, rotationZ: 0
            });
            // Q30 deitada ao longo de X
            this.components3D.push({
                uid: 'comp_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9),
                catalogId: pePeca.id, codigo: pePeca.codigo, tipo: pePeca.tipo,
                length: peLength, nome: pePeca.nome, peso: pePeca.peso,
                x: col.x, y: baseY, z: col.z,
                rotationX: 0, rotationY: 0, rotationZ: 0
            });
            // Q30 deitada ao longo de Z
            this.components3D.push({
                uid: 'comp_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9),
                catalogId: pePeca.id, codigo: pePeca.codigo, tipo: pePeca.tipo,
                length: peLength, nome: pePeca.nome, peso: pePeca.peso,
                x: col.x, y: baseY, z: col.z,
                rotationX: 0, rotationY: 90, rotationZ: 0
            });
        });
    }
}
```

#### 3b. Colunas sem sapataOffset

Alterar as colunas para começar de `baseY` em vez de `baseY + sapataOffset`:

```diff
- buildTrussLine3D(startX, baseY + sapataOffset, 0, startX, topY - cuboHalf, 0, 0, 0, 90);
- buildTrussLine3D(endX, baseY + sapataOffset, 0, endX, topY - cuboHalf, 0, 0, 0, 90);
+ buildTrussLine3D(startX, baseY, 0, startX, topY - cuboHalf, 0, 0, 0, 90);
+ buildTrussLine3D(endX, baseY, 0, endX, topY - cuboHalf, 0, 0, 0, 90);
```

E para as colunas traseiras (se width > 0.5):
```diff
- buildTrussLine3D(startX, baseY + sapataOffset, -width, startX, topY - cuboHalf, -width, 0, 0, 90);
- buildTrussLine3D(endX, baseY + sapataOffset, -width, endX, topY - cuboHalf, -width, 0, 0, 90);
+ buildTrussLine3D(startX, baseY, -width, startX, topY - cuboHalf, -width, 0, 0, 90);
+ buildTrussLine3D(endX, baseY, -width, endX, topY - cuboHalf, -width, 0, 0, 90);
```

Remover variável `sapataOffset`.

#### 3c. Remover sapata padrão

Remover a variável `sapataPeca` e o bloco de sapatas padrão (linhas 1191-1207).

Remover do seletor UI a opção "Sapata Padrão" (linha 35-41 do HTML):
```diff
- <option value="padrao">Sapata Padrão</option>
```

#### 3d. Sleeves automáticos entre segmentos Q30

Em `buildTrussLine3D()`, dentro do `forEach` de segmentos:

```javascript
bestComb.forEach((fitPeca, idx) => {
    const pLen = fitPeca.comprimento;
    const cX = curX + (pLen / 2) * dirX;
    const cY = curY + (pLen / 2) * dirY;
    const cZ = curZ + (pLen / 2) * dirZ;

    this.components3D.push({
        uid: 'comp_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9),
        catalogId: fitPeca.id, codigo: fitPeca.codigo, tipo: fitPeca.tipo,
        length: fitPeca.comprimento, nome: fitPeca.nome, peso: fitPeca.peso,
        x: cX, y: cY, z: cZ,
        rotationX: rotX, rotationY: rotY, rotationZ: rotZ
    });

    if (virtualStock[fitPeca.id]) virtualStock[fitPeca.id]--;
    curX += pLen * dirX;
    curY += pLen * dirY;
    curZ += pLen * dirZ;

    // Sleeve na junção entre segmentos (não após o último)
    if (idx < bestComb.length - 1) {
        const sleeveCat = this.catalog.find(p => p.tipo === 'sleeve');
        if (sleeveCat) {
            this.components3D.push({
                uid: 'comp_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9),
                catalogId: sleeveCat.id, codigo: sleeveCat.codigo, tipo: sleeveCat.tipo,
                length: 0, nome: sleeveCat.nome, peso: sleeveCat.peso,
                x: curX, y: curY, z: curZ,
                rotationX: rotX, rotationY: rotY, rotationZ: rotZ
            });
        }
    }
});
```

#### 3e. Validação de sleeve no início

Adicionar nas validações do `generateAutoLayout()`:
```javascript
const sleevePeca = this.catalog.find(p => p.tipo === 'sleeve');
if (q30s.length === 0 || !cuboPeca || !sleevePeca) {
    alert("Catálogo incompleto! Cadastre barras Q30, Cubo Conector e Sleeve no estoque.");
    return;
}
```

### Fase 4: Limpeza de sapata em outras partes

#### 4a. Remover `createSapataModel()` do 3D

Remover a função `createSapataModel` (linhas 1777-1797) e o bloco `else if (comp.tipo === 'sapata')` no loop 3D.

#### 4b. Remover renderização de sapata em 2D

Remover os 3 blocos `else if (comp.tipo === 'sapata')` do `syncCanvasFrom3D()` (vistas frontal, superior, lateral).

#### 4c. Remover sapata do catálogo UI

Em `pecas/index.php`, remover a opção `sapata` do seletor de tipo se não for mais necessário cadastrar sapatas.

---

## Riscos e Observações

1. **SideConnectingBeams**: `addSideConnectingBeams()` usa `virtualStock`. Com Pé de Galinha consumindo Q30 do estoque, o estoque virtual pode ficar negativo. Verificar lógica.

2. **Cubo na base**: O cubo na base vai aparecer nas vistas 2D e 3D. Em 2D frontal, o cubo na base pode ficar visualmente sobreposto com o primeiro segmento da coluna — aplicar mesmo destaque visual (stroke largo) que nos cubos do topo.

3. **Q30 deitadas nas vistas 2D**: Nas vistas frontal/superior/lateral, as Q30 deitadas ao longo de X (rotationZ=0) aparecem como barras horizontais. As ao longo de Z (rotationY=90) aparecem como pontos/curtos na vista frontal. Verificar renderização consistente.

4. **Sleeves aumentam quantitativo**: Correto construtivamente, mas exige sleeves cadastrados.
