# Observações (staging — status: observed)
Itens aqui ainda NÃO são regra. Promovem para learned_patterns.md ao atingir evidence >= 3.

---
id: obs-015
type: bug
status: observed
confidence: 65
evidence: 1
first_seen: 2026-08-18
last_verified: 2026-08-18
superseded_by: null
tags: [trussCalculator, syncCanvasFrom3D, selecao, fabricjs]
source_commit: ""
---
# syncCanvasFrom3D() derruba a seleção ativa do Fabric.js (não só no painel de altura)
`syncCanvasFrom3D()` faz `canvas.clear()` + recria TODOS os objetos Fabric do zero a
cada chamada — o objeto antigo que estava selecionado deixa de existir, então
`canvas.getActiveObject()` fica vazio depois. Isso foi CORRIGIDO especificamente no
fluxo do painel de altura (`_setSelectedHeight` -> `_reselectComp(uid)`, 2026-08-18),
mas `syncCanvasFrom3D()` é chamado em dezenas de lugares (drag/object:modified, rotação,
duplo-clique, delete, undo/redo, mudança de escala/dimensões...) — qualquer um desses
fluxos que rode com uma peça selecionada provavelmente também perde a seleção
visualmente após a ação, mesmo quando isso não quebra nada funcionalmente (a maioria
não depende de "continuar selecionado" pra funcionar, só o painel de altura dependia).
Não investigado a fundo quais fluxos isso afeta visivelmente; se aparecer mais
reclamação de "desmarcou sozinho", o fix é sempre o mesmo padrão: `_reselectComp(uid)`
depois do `syncCanvasFrom3D()` relevante.

---
id: obs-013
type: mistake
status: observed
confidence: 100
evidence: 1
first_seen: 2026-08-17
last_verified: 2026-08-17
superseded_by: null
tags: [api, contrato, documentacao, stale]
source_commit: ""
---
# api_patterns.md documenta `{success, data}` mas o código real usa `{ok, data}`
Verificado via curl direto em `/api/pecas` e `/api/pecas` POST (ApiController::listarPecas/
salvarPeca): toda resposta JSON usa a chave `ok` (bool), não `success` como `.ai/context/
api_patterns.md` afirma ("Toda resposta JSON deve conter `success`..."). O frontend
(`pecasManager()` em `pecas/index.php`, `trussCalculator()` em `home/index.php`) já
consome `res.ok` corretamente — o código está consistente consigo mesmo, só a doc estava
desatualizada/nunca esteve certa.
CORRIGIDO em 2026-08-18: `.ai/context/api_patterns.md` e `.ai/rules/api_rules.md`
atualizados para `ok` (pedido do usuário: "documente tudo").

---
id: obs-014
type: bug
status: observed
confidence: 70
evidence: 1
first_seen: 2026-08-17
last_verified: 2026-08-17
superseded_by: null
tags: [pecas, admin, comprimento, plana, braco, form]
source_commit: ""
---
# Peças & Estoque (admin) zera silenciosamente o comprimento de plana/braco ao salvar
Em `pecasManager().savePeca()` (`app/views/pecas/index.php`), o campo comprimento fica
`disabled` a menos que `tipo` seja Q30/cubo/grepo/sapata (agora também montante/travessa),
E a lógica de ajuste força `comprimento = 0` para qualquer tipo fora de
Q30/grepo/cubo/sapata — isso inclui `plana` e `braco`, que TÊM comprimento real
(PLANA-200/250/300, BRACO-300, cadastrados só via seed.sql). Criar ou editar uma peça
`plana`/`braco` pela UI hoje zera o comprimento dela. Não corrigido nesta sessão (fora do
escopo — só evitei que montante/travessa caíssem no mesmo problema). Fix sugerido: trocar a
condição de "lista de exceção" por "zerar só para sleeve" (o único tipo sem comprimento
próprio).

---
id: obs-009
type: bug
status: observed
confidence: 95
evidence: 1
first_seen: 2026-06-11
last_verified: 2026-06-11
superseded_by: null
tags: [trussCalculator, uid, loadProject, drag, object-modified]
source_commit: ""
---
# Colisão de uid após loadProject: arrastar uma peça move outra junto
`_uid()` é um contador de sessão (`comp_` + `_uidCounter++`, inicia em 0 a cada
carga de página). `saveProject()` serializa `components3D` COM os uids, e
`loadProject()` (formato novo, linha ~2027) restaura os comps com os uids salvos
SEM reposicionar `_uidCounter`. Resultado: após recarregar a página, carregar um
projeto e adicionar peça nova, `_uid()` gera `comp_1` de novo — uid duplicado.
Como todos os fluxos usam `components3D.find(c => c.uid === ...)` (object:modified,
nudge, snap, rotação) e delete/rotate usam `includes(c.uid)`, arrastar qualquer
uma das peças com uid duplicado grava a posição na PRIMEIRA do array — a outra
peça "vem junto"/pula após `syncCanvasFrom3D()`; deletar uma deleta as duas.
Fix sugerido: em `loadProject`, re-derivar `_uidCounter` do maior sufixo numérico
dos uids carregados (ou regenerar uids), e/ou usar uid não-sequencial
(timestamp+random).
CORRIGIDO em 2026-06-11: `loadProject` (formato novo) re-deriva `_uidCounter`
do maior sufixo e atribui uid novo a comps sem uid.

---
id: obs-010
type: bug
status: observed
confidence: 80
evidence: 1
first_seen: 2026-06-11
last_verified: 2026-06-11
superseded_by: null
tags: [trussCalculator, magneticSnap, overlap, drag]
source_commit: ""
---
# magneticSnap permite sobreposição total (centro-a-centro) — peças "grudam"
`magneticSnap()` casa 9x9 pontos da bounding box (cantos, meios E CENTRO) do alvo
contra os mesmos 9 pontos de cada objeto. O par centro-centro faz a peça arrastada
saltar para EXATAMENTE em cima da vizinha (sobreposição total) quando passa a
menos de `snapPx = 0.1 * scale` px do centro dela. Com snap sempre ativo (sem
toggle desde 2026-06-11), o usuário percebe como "peça gruda nas outras".
Fix sugerido: remover o ponto central da lista (snap só borda/aresta) ou só
permitir centro-centro em eixo único (alinhamento), nunca nos dois eixos.
CORRIGIDO em 2026-06-11: ponto central removido de tPoints/oPoints.

---
id: obs-011
type: bug
status: observed
confidence: 60
evidence: 1
first_seen: 2026-06-11
last_verified: 2026-06-11
superseded_by: null
tags: [trussCalculator, addComponent, cascata, overlap]
source_commit: ""
---
# Cascata anti-sobreposição de addComponent falha com peças rotacionadas/desalinhadas
`addComponent()` só detecta sobreposição se os OUTROS eixos casarem com tol=0.05m
e mede o tamanho da peça existente sempre no eixo de cascata (`c.length`), mesmo
quando a peça está rotacionada (ex: coluna vertical tem o comprimento em Y, não X).
Peças existentes com y/z ligeiramente diferentes (>0.05m) não são vistas e a nova
peça nasce exatamente em cima delas, no centro do canvas. Agravante: o gap é de
só 0.05m, então mesmo quando a cascata funciona a peça nova nasce visualmente
encostada na vizinha.
CORRIGIDO em 2026-06-11: helper `sizeAlong(c)` ciente de orientação
(isQ30AlongZ/isQ30Column), tol=0.30m, gap=0.10m.
SUBSTITUÍDO em 2026-08-18: toda a heurística de `sizeAlong`/`tol`/`hasOverlap`
foi trocada por colisão real de caixa orientada → AABB no mundo
(`collidesWithOthers`/`_worldAABB`), que lida corretamente com qualquer
rotação (não só os casos discretos de isQ30AlongZ/isQ30Column). Ver
`.ai/summaries/current_state.md` (2026-08-18).

---
id: obs-005
type: pattern
status: observed
confidence: 80
evidence: 1
first_seen: 2026-06-10
last_verified: 2026-06-10
superseded_by: null
tags: [trussCalculator, performance, findCombs, auto-layout, sketch]
source_commit: ""
---
# findCombs() — busca exaustiva de combinações repetida sem memoização
`generateTrussLine()` e `addSideConnectingBeams()` implementam uma função recursiva
`findCombs(target, index, currentComb)` que enumera TODAS as combinações de peças Q30 que somam
exatamente o vão, antes de pontuar por penalidade de estoque. Para vãos grandes (~15-20m+, comuns
em estruturas de palco) com denominações {0.5,1,1.5,2,3,4,5}, o número de combinações cresce
rapidamente (padrão de partição de inteiros) e pode travar o navegador.
Além disso, como o `virtualStock` é compartilhado entre as chamadas de frente e fundo, vigas/colunas
geometricamente simétricas podem receber composições de peças diferentes (assimetria visual) quando
o estoque de alguma peça é insuficiente.
Sugestão: memoizar por `target` restante (DP), ou limitar a busca por tempo/profundidade com
fallback guloso garantido.

---
id: obs-006
type: pattern
status: observed
confidence: 75
evidence: 1
first_seen: 2026-06-10
last_verified: 2026-06-10
superseded_by: null
tags: [trussCalculator, validacao, dimensoes, auto-layout]
source_commit: ""
---
# Vãos não múltiplos de 0,5m deixam buraco silencioso na estrutura
Em `generateTrussLine()`, o fallback guloso (`while (remaining > 0.05) { ... if (!fitPeca) break; }`)
para sem preencher o restante quando `remaining < 0.5m` (menor peça Q30 do catálogo) e nenhuma
combinação exata existe. Não há validação no formulário de `dimensions.width/height/length` para
garantir múltiplos de 0.5m, nem aviso ao usuário sobre o vão não preenchido.

---
id: obs-007
type: pattern
status: observed
confidence: 70
evidence: 1
first_seen: 2026-06-10
last_verified: 2026-06-10
superseded_by: null
tags: [trussCalculator, dimensoes, ui, regua]
source_commit: ""
---
# getTrussSegmentsForDistance() usa algoritmo diferente de generateTrussLine()
A função usada para exibir segmentos nas réguas/labels de medida (`getTrussSegmentsForDistance`)
usa busca gulosa simples (sempre a maior peça que cabe), enquanto `generateTrussLine()` usa busca
exaustiva + penalização por estoque. Para o mesmo vão, o número/composição de segmentos exibido na
régua pode não bater com o que o Auto-Layout/Sketch realmente gera.

---
id: obs-008
type: pattern
status: observed
confidence: 70
evidence: 1
first_seen: 2026-06-10
last_verified: 2026-06-10
superseded_by: null
tags: [trussCalculator, cubo, geometria, hardcode]
source_commit: ""
---
# cuboHalf = 0.15 hardcoded em 3 lugares (DRY)
`generateAutoLayout()`, `addSideConnectingBeams()` e `generateFromSketch()` usam a constante
literal `cuboHalf = 0.15` em vez de derivar de `cuboPeca.comprimento / 2` (CUBO no catálogo tem
comprimento 0.30m). Hoje os valores batem, mas se o comprimento do CUBO for editado em
Peças & Estoque, a geometria/posicionamento de juntas desalinha silenciosamente.

---
id: obs-001
type: pattern
status: observed
confidence: 95
evidence: 1
first_seen: 2026-06-09
last_verified: 2026-06-09
superseded_by: null
tags: [alpine, fabricjs, reactivity, bug]
source_commit: ""
---
# Conflito de Reatividade entre Alpine.js e Bibliotecas Externas Complexas (ex: Fabric.js Canvas)
Quando instâncias complexas (como `fabric.Canvas`) são armazenadas diretamente em propriedades de dados do Alpine.js (ex: `this.canvas = new fabric.Canvas(...)`), o Alpine envolve o objeto e suas propriedades internas recursivamente em proxies de reatividade.
Isso corrompe os ponteiros de método internos e arrays nativos da biblioteca externa, resultando em erros como `TypeError: e[i].render is not a function` ao iterar por coleções internas.
A solução consiste em isolar tais instâncias fora do escopo reativo do Alpine, salvando-as como variáveis locais de fechamento (closure variables) dentro da função de dados ou no objeto `window`, acessando-as diretamente sem passar pela reatividade do `this`.

---
id: obs-002
type: pattern
status: observed
confidence: 90
evidence: 1
first_seen: 2026-06-09
last_verified: 2026-06-09
superseded_by: null
tags: [projection, topview, 2d, 3d, truss]
source_commit: ""
---
# Projeção Dinâmica 2D Vista Superior a partir da Vista Frontal (Planta Baixa)
Para estruturas Box Truss modeladas interativamente em uma única vista 2D (frontal), é altamente eficiente e menos propenso a erros projetar a Vista Superior (Planta Baixa) de forma somente-leitura.
A planta baixa projeta a largura (profundidade) do portal e renderiza a duplicação dos quadros dianteiro/traseiro, adicionando barras de treliças laterais, sleeves e sapatas correspondentes aos eixos X e Z (representados verticalmente na tela).
Essa abordagem unifica o modelo de dados sem a complexidade de sincronização bidirecional de edição em múltiplos planos.

---
id: obs-003
type: bug
status: observed
confidence: 100
evidence: 1
first_seen: 2026-06-09
last_verified: 2026-06-09
superseded_by: null
tags: [syntax-error, trussCalculator, alpine, fabricjs, 2d-render]
source_commit: ""
---
# SyntaxError no bloco sleeve da renderização 2D (obj = new fabric.Rect({) ausente)
O método de renderização 2D do componente `trussCalculator()` continha um erro de sintaxe JavaScript no branch `comp.tipo === 'sleeve'`:
- A chamada `obj = new fabric.Rect({` estava omitida (linha começava diretamente com `height: sleeveSize,`)
- Isso causava `SyntaxError: Unexpected token ':'` porque o parser JS interpretava as propriedades como labeled statements
- O erro invalidava todo o bloco `<script>` (~2394 linhas), impedindo `trussCalculator()` de ser definida globalmente
- Consequência: todos os Alpine Expression Errors com "is not defined" (dimensions, catalog, canvasScale, etc.)
- Fix: adicionar `obj = new fabric.Rect({` com `left`, `top`, `width` antes das demais propriedades

---
id: obs-004
type: pattern
status: observed
confidence: 95
evidence: 1
first_seen: 2026-06-09
last_verified: 2026-06-09
superseded_by: null
tags: [sapata, pe-de-galinha, 2d, 3d, catalog, auto-layout]
source_commit: ""
---
# Sapatas Pé de Galinha com tamanhos variáveis (1m e 2m)
Foram criadas duas novas variantes de sapata no banco de dados:
- SAPATA1: "Sapata Pé de Galinha 1m" (comprimento=1.0, peso=1.5kg, preco=R$55)
- SAPATA2: "Sapata Pé de Galinha 2m" (comprimento=2.0, peso=3.0kg, preco=R$85)

Mudanças no código:
- seed.sql: adicionados SAPATA1 e SAPATA2 (SAPATA original mantida para compatibilidade)
- pecas/index.php: formulário agora permite comprimento > 0 para sapatas; tabela exibe comprimento
- home/index.php catalog: exibe "1m"/"2m" em vez de "SP" para sapatas com comprimento
- home/index.php 2D frontal/lateral: sapataWidth/Height escala com comp.length (25+5*len)/50
- home/index.php 2D superior: sapataSize escala com comp.length (25+5*len)/50
- home/index.php 3D: createSapataModel(compLength) parametrizado (plate 0.3+0.1*len, conector proporcional)
- home/index.php 3D label: mostra "1.0m"/"2.0m" em vez de "SP"
- home/index.php auto-layout: armazena comprimento real no campo length do componente
- addComponent: length=peca.comprimento suporta valores >0 para sapatas
