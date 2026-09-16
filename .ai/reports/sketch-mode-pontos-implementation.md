# Sincronização e Melhorias 2D — Sessão 2026-06-10

## Bugs corrigidos na sincronização entre vistas

### object:moving — fundo e lateral_dir

| Vista | Coord | Erro | Correção |
|-------|-------|------|----------|
| Fundo | dx | `newX + comp.x` | `newX - comp.x` |
| Fundo | left (grupo) | `cx - (otherComp.x - dx)` | `cx - (otherComp.x + dx)` |
| Lat Dir | dz | `newZ + comp.z` | `newZ - comp.z` |
| Lat Dir | left (grupo) | `cx - (otherComp.z - dz + depth/2)` | `cx - (otherComp.z + dz + depth/2)` |

### object:modified — fundo e lateral_dir

| Vista | Coord | Erro | Correção |
|-------|-------|------|----------|
| Fundo | dx | `newX + comp.x` | `newX - comp.x` |
| Lat Dir | dz | `newZ + comp.z` | `newZ - comp.z` |

### Pé de Galinha — dimensão corrigida

| Tipo | Antes | Agora | Total |
|------|-------|-------|-------|
| pg1 (1m total) | cada Q30 = 1.0m (total 2.30m) | cada Q30 = 0.5m (total 1.30m) | ✅ |
| pg2 (2m total) | cada Q30 = 2.0m (total 4.30m) | cada Q30 = 1.0m (total 2.30m) | ✅ |

### Algoritmo fallback greedy

**Problema:** Quando não havia combinação exata de Q30s para preencher o vão, o fallback adicionava uma Q30 maior que o espaço restante, penetrando o cubo.

**Correção:** O fallback agora itera `for` (maior para menor) e só usa a Q30 se `comprimento <= remaining + 0.01`. Se nenhuma couber, **para** (deixa gap).

### Dim display revertido

Dimensões 2D/3D agora mostram o valor **configurado** (length, height, width), sem `+ 0.30`.

## Cubo = mesma treliça da Q30

Antes: `createCubeModel()` custom (4 tubos + 2 placas). Agora: `createColoredTruss(0.30, cuboMat)` que usa `createTrussSection` (tubos 32mm + travessas + diagonais + placas) com material laranja.

## Panorâmica e zoom

| Funcionalidade | Implementação |
|---------------|--------------|
| **Zoom scroll** | `mouse:wheel` no cursor (existente) |
| **Pan** | Espaço + clique esquerdo + arrasto (novo) |
| **Fit to view** | `fitToView()` — calcula zoom ideal |
| **Reset** | `resetZoom()` — agora também reseta pan |
| **Instruções** | Atualizadas nas 3 vistas 2D |

## Arquivos alterados

- `app/views/home/index.php` — todos os bugs + melhorias
- `.ai/reports/assembly_rules.md` — atualizado
- `.ai/reports/errors_and_fixes.md` — atualizado
