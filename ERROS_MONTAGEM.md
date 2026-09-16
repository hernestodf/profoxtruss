# Erros de Montagem — Status das Correções

TODOS os erros identificados na auditoria foram corrigidos.

## ✅ Corrigidos

| Item | Descrição | Status |
|------|-----------|--------|
| A1 | Coluna encurtada na base (cuboHalf) | ✅ |
| A2 | UID sequencial sem colisão | ✅ |
| A3 | Sleeves removidos da geração automática | ✅ |
| A4 | Grepo → Cubo nos cantos (auto + sketch) | ✅ |
| A4 | Grepo removido da geração automática | ✅ |
| C1 | Ângulos livres no sketch (Math.atan2) | ✅ |
| C8 | Fixadores estimados no BOM | ✅ |
| — | Dimensões físicas totais (+0.30m) | ✅ |
| — | Pé de Galinha: duas Q30s sem overlap | ✅ |
| — | Cubo = sólido 0.30m, Q30 termina na face | ✅ |

## 📋 Regras de Montagem Vigentes

| Situação | Conector | Geração |
|----------|----------|---------|
| Junção L (canto 90°) | CUBO | Automática |
| Junção T (três vias) | CUBO | Automática |
| Emenda linear (mesmo eixo) | NENHUM (conexão direta) | Automática |
| Conexão entre estruturas | GREPO | Manual |
| Içamento / elevação | SLEEVE 4 FACES P30 | Manual |
| Pendurar equipamentos | GRAPPLE | Manual |
| Base no chão | PÉ DE GALINHA (cubo + 2×Q30) | Automática |

## 🔧 Componentes A Implementar

| Componente | Prioridade |
|------------|------------|
| Grapple (aço ASTM A36, perfil U) | Média |
| Cubo 5 Faces P30 (alumínio 6351-T6) | Média |
| Linha P30 (tubos 2", cantoneiras L 4"×3/8") | Futuro |
| Q25 (perfil 25×25cm leve) | Futuro |

## ✅ Sleeve 4 Faces P30 — Parcialmente Implementado

| Aspecto | Status |
|---------|--------|
| Tipo `sleeve_4faces` reconhecido no catálogo | ✅ Badge "MÓVEL" |
| Renderização 2D (cor roxa, forma sleeve) | ✅ |
| Renderização 3D (modelo sleeve) | ✅ |
| Label "SF" no 3D | ✅ |
| Posicionamento em qualquer ponto da Q30 | ⏳ Pendente (via drag-and-drop no canvas) |
| Deslizamento/travel interno | ⏳ Pendente (futuro) |
