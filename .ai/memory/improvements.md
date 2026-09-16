# improvements
## SAPATA — Base de Apoio Retangular (2026-06-15)
- Nova peça `tipo: 'sapata'` no catálogo: base de apoio sólida para torres Box Truss.
- 3 modelos no banco: SAPATA-30X50 (0.50m), SAPATA-30X60 (0.60m), SAPATA-30X80 (0.80m).
- Geometria: retângulo sólido com 4 faces estruturais, largura fixa 0.30m, altura fixa 0.035m (3.5cm).
- Constraints de solo: `compY = 0.0175` (origem centralizada, base em Y=0) — aplicado em
  `addComponent`, `object:modified` e `nudgeObject`. SAPATA nunca fica suspensa.
- 2D (5 views): retângulo com dimensão fina mínima de 3px (`Math.max(0.035 * scale, 3)`).
  Frontal/Fundo = 0.30m × 0.035m apoiado no chão; Superior = 0.30m × comprimento (planta);
  Lateral/Dir = comprimento × 0.035m apoiado no chão. Cor âmbar `#d97706`.
- 3D: `createSapataModel(length)` — `BoxGeometry(0.30, 0.035, length)` sólido com
  material aço escovado (`color: '#b8c5d6', metalness: 0.92, roughness: 0.38`).
  Borda chamfrada no topo (edge detail com material mais claro `#d1d5db`).
- Label 3D: "SAPATA" cor âmbar `#d97706`.
- Quantitativo: incluído em metros lineares e contagem de peças.
- Catálogo/Peças: dropdowns de tipo, badge âmbar, label "SP" no toolbar card.
  Comprimento editável (default 0.50m se vazio), campo habilitado no formulário.
- Rotação livre em todos os eixos (rotationX/Y/Z) sem `applyQ30Rotation`.
- Integrado em: save/load de projeto, undo/redo, copiar/colar/duplicar, PDF, Excel.
- Ver `.ai/context/business_rules.md` (regras SAPATA) e `.ai/context/terminology.md`.

## Imagem PNG/JPG como preenchimento da Lona (2026-06-11)
- Botões 📷 (aplicar) e 📷🚫 (remover) na toolbar de acessórios + input file
  oculto (accept png/jpeg). Fluxo: selecionar lona(s) no canvas → 📷 →
  escolher arquivo. Sem lona selecionada, toast orienta.
- `applyLonaImage()`: lê o arquivo (FileReader), reduz para máx. 1024px no
  maior lado via canvas (PNG mantém PNG p/ transparência; JPG re-encoda a
  0.85) e grava o dataURL em `comp.image` → vai no JSON do projeto
  (save/load/copiar/colar funcionam sem mudanças). Coluna projects.components
  é TEXT (SQLite, sem limite prático).
- 2D: `_getLonaImage(comp)` usa `lonaImgCache` (CLOSURE, fora do Alpine —
  regra obs-001) com carregamento async: devolve null até carregar e chama
  `syncCanvasFrom3D()` no onload. Na view Frontal/Fundo o fill vira
  `fabric.Pattern` esticado pro retângulo (patternTransform); nas views de
  ponta/planta (lona fina) mantém cor sólida.
- 3D: `accMat.map = TextureLoader().load(dataURL)`, cor branca (não tinge) e
  opacity 0.95; aparece quando carrega (loop `animate` já re-renderiza).
- `removeLonaImage()` apaga `comp.image` e volta à cor sólida.

## Cor configurável para a Lona (2026-06-11)
- Novo seletor `<input type="color">` na toolbar de acessórios
  (`accessoryColor`, default #e2e8f0 = cor antiga): novas lonas nascem com
  `comp.color`; trocar a cor com lona(s) selecionada(s) recolore na hora via
  `applyAccessoryColor()` (com toast + pushHistory).
- 2D (5 views): stroke/cornerColor = `comp.color`, fill via novo helper
  `_hexToRgba(hex, 0.25)` — transparência mantida. 3D: `color: comp.color`
  no material translúcido (opacity 0.55 inalterada).
- Lonas antigas (sem `color` salvo) caem no fallback `|| '#e2e8f0'` /
  `|| '#f8fafc'` — projetos salvos continuam idênticos.
- Painel de LED não foi alterado (textura própria).

## Copiar/Colar/Duplicar componentes — Ctrl+C / Ctrl+V / Ctrl+D (2026-06-11)
- Novos métodos `copySelected()`/`pasteClipboard()` + estado `_clipboard`
  (snapshot JSON dos comps selecionados; sobrevive a trocas de view).
- Funciona com seleção única e múltipla (activeSelection); todos os tipos de
  peça (Q30/plana/braco/conectores/acessórios/labels) — deep-clone preserva
  width/height/depth/text/rotações; cópia ganha uid novo via `_uid()`.
- Cola com offset de 0,5m nos eixos visíveis da view atual (superior: x+z;
  laterais: z; frontal/fundo: x), acumulado NO clipboard → colar várias vezes
  faz "escadinha". Cópias ficam selecionadas (fabric.ActiveSelection — Fabric
  local é 5.3.0) para arrastar em seguida.
- Atalhos no keydown global (só nas views 2D, fora de inputs): Ctrl+C só faz
  preventDefault quando há objeto de canvas selecionado (não interfere na
  cópia de texto da página); Ctrl+D = copiar+colar imediato. Toasts de
  feedback; undo/redo via pushHistory após colar. Hint da view frontal
  atualizado.

## Fix: duplo-clique não alternava horizontal/vertical nas views laterais (2026-06-11)
- Sintoma: duplo-clique numa peça na Vista Lateral Esquerda (e Lateral Dir.)
  não alternava entre horizontal e vertical.
- Causa: o handler `mouse:dblclick` alternava o campo de rotação "cru" da view
  (`rotationX` nas laterais), mas a classificação canônica de orientação das
  peças lineares (Q30/plana/braco) usa `isQ30AlongZ` (rotationY) e
  `isQ30Column` (rotationZ) — nas laterais, horizontal = alongZ (rotationY=90)
  e vertical = coluna (rotationZ=90); rotationX não participa, então alternar
  rotationX era inócuo. Bug parcial análogo na frontal: peça alongZ não saía
  da profundidade alternando rotationZ (isQ30AlongZ é checado primeiro).
- Fix: para peças Q30-like, o duplo-clique agora calcula o ângulo atual NA
  VIEW (mesma fórmula do `applyQ30Rotation`) e aplica o toggle 0↔90 via
  `applyQ30Rotation(comp, viewMode, toggle(curAngle))` — escrevendo o trio
  canônico rotationX/Y/Z. Conectores/sleeve/acessórios mantêm o toggle antigo
  do campo da view.

## CORREÇÃO do Braço: é 1 barra linear, NÃO uma treliça Q30 (2026-06-11)
- O usuário corrigiu a definição: BRAÇO = apenas 1 barra estrutural (1 tubo
  longitudinal), sem faces, não forma treliça; elemento linear com só
  comprimento e direção. Taxonomia registrada em business_rules.md:
  box_truss faces:4 / trelica_plana faces:2 / braco faces:1.
- **3D**: `createColoredTruss` substituído por `createBracoModel(length)` —
  um único `CylinderGeometry` (raio 0.025m) ao longo de X.
- **2D**: braço agora é fino (thinT = max(0.05*scale, 3px)) em TODAS as
  direções transversais nas 5 views (a plana é fina só na vista de ponta;
  o braço é fino sempre, inclusive altura).
- **Banco/seed**: nome "Braço 3.00m (barra)", peso 1,5kg (era 4,8 de treliça),
  preço R$120 (era 310). Form de peças: "Braço (barra)".
- A entrada anterior (abaixo) descrevendo o braço como treliça Q30 teal está
  SUPERSEDED por esta.

## Nova peça "Braço" (tipo `braco`) — barra de Q30 de 3m (2026-06-11)
- Definição do usuário: "BRAÇO é uma barra de Q30 de 3 metros" — mesma
  geometria/seção do Q30, mas peça de catálogo própria (código/estoque
  separados).
- **Banco**: BRACO-300 ("Braço Q30 3.00m", tipo `braco`, 3m, 4,8kg, R$310,
  estoque 5) no SQLite vivo + `seed.sql`. Form de Peças & Estoque com opção
  "Braço Q30".
- **Comportamento = Q30** (mesmo tratamento dado à PLANA): incluído em
  `applyQ30Rotation` (call sites), `sizeAlong` da cascata, metros lineares,
  badge do catálogo com comprimento.
- **Render**: cor teal (#2dd4bf) no 2D (`getComponentColors`, seção cheia
  0,30m — diferente da PLANA que é fina) e no 3D via
  `createColoredTruss(comp.length, bracoMat)` (reuso da treliça Q30 completa,
  4 banzos). Label 3D com comprimento em teal.

## Nova peça estrutural "Treliça Plana" (tipo `plana`) — 2m / 2,5m / 3m (2026-06-11)
- Pedido do usuário, com especificação técnica ditada (registrada em
  `.ai/context/business_rules.md` e `terminology.md`): treliça de 2 faces
  paralelas ligadas por diagonais, sem volume fechado (Box Truss "aberto"),
  seção 0,30m de altura x ~0,05m de espessura.
- **Banco**: 3 produtos novos (PLANA-200/250/300, tipo `plana`, pesos
  1,8/2,2/2,7kg, preços 140/165/190, estoque 5) inseridos no SQLite vivo e no
  `seed.sql`. Form de Peças & Estoque ganhou a opção "Treliça Plana Q30".
- **Catálogo (card)**: badge lime com o comprimento (igual Q30).
- **Comportamento = Q30**: `applyQ30Rotation` (10 call sites agora aceitam
  `plana`), `sizeAlong` da cascata anti-sobreposição, metros lineares do
  quantitativo. `isQ30AlongZ`/`isQ30Column` são genéricos (leem rotações).
- **2D**: cor lime (#a3e635) em `getComponentColors`; convenção "plano da
  treliça sempre vertical": frontal/fundo mostram face plena (comprimento x
  0,30m); vista de ponta/planta mostram espessura fina `planaThin =
  max(0.05*scale, 3px)` (frontal: alongZ fino; superior: crossThickness fino
  em todas as orientações exceto... coluna mostra 0,30 x fino; lateral: coluna
  e vista de ponta finas, alongZ face plena).
- **3D**: `createFlatTrussSection(length)` — 2 banzos (tubos a y=±0,15 após a
  rotação padrão p/ eixo X), travessas a cada ~0,4m e diagonais em zigue-zague,
  tudo num único plano; material lime `planaMat`. Label 3D mostra
  comprimento em lime.
- **Fora de escopo (decisão)**: geração via Sketch e contagem de ferragens
  continuam exclusivas de Q30; PLANA é adição manual pelo catálogo.

## Acessório "PAR LED" (refletor) na toolbar (2026-06-11)
- Pedido do usuário: botão nos acessórios para adicionar um PAR LED (referência
  visual: modelo Sketchfab "parled lighting bawah"). Carregar o GLB do Sketchfab
  exigiria download autenticado + GLTFLoader (projeto usa assets locais:
  three.min.js + OrbitControls.js apenas) → optou-se por modelo PROCEDURAL,
  consistente com Painel LED/Grepo/Sleeve.
- Novo tipo `'parled'` (codigo PARLED, `excludeFromQuantitative: true`),
  tamanho fixo 0.25×0.30×0.25m (luminária, ignora inputs WxH; sem modo
  desenhar 2 cantos). Botão 🔦 na toolbar de acessórios →
  `addAccessory('parled')`.
- 2D: helper `_createParLed2D(left, top, angle, uid)` — `fabric.Group` (corpo
  arredondado escuro + lente circular âmbar #fbbf24), usado nas 5 views
  (frontal/fundo giram com rotationZ, superior rotationY, laterais rotationX);
  raio mínimo 5px p/ escalas pequenas; arrastável/rotacionável (mtr)/deletável
  via mecanismos genéricos por uid; snap magnético ligado.
- 3D: `createParLedModel()` — THREE.Group com corpo `CylinderGeometry`
  (0.11/0.13×0.25, apontando +Z = público), lente `CircleGeometry` emissiva
  âmbar (intensity 1.6) e garra em "U" invertido no topo (2 braços + travessa)
  para pendurar no truss.
- Sem etiqueta de medida no 3D (incluído no skip de acessórios) e fora do
  quantitativo/PDF/Excel (excludeFromQuantitative).

## Pan por clique-e-arrasto no fundo do canvas 2D (2026-06-11)
- Pedido do usuário: poder clicar no fundo (área vazia) e arrastar para mover a
  vista, sem precisar de tecla.
- `mouse:down` do pan agora aceita: botão do meio, Espaço+arrasto OU botão
  esquerdo no fundo (`!opt.target`) sem Shift e fora dos modos Sketch/Acessório
  (`sketchMode`/`accessorySketchType`, onde clique no fundo adiciona pontos).
  **Shift+arrasto no fundo preserva a seleção retangular múltipla.**
- 2 bugs pré-existentes corrigidos no mesmo bloco:
  1. A condição usava `opt.e.target === null` (target do MouseEvent DOM — é o
     elemento `<canvas>`, nunca null) em vez de `opt.target` (objeto Fabric sob
     o cursor) → o pan por Espaço+arrasto NUNCA disparava; só botão do meio
     funcionava. Corrigido para `!opt.target`.
  2. `mouse:move` fazia `_panVpt[4] += dx` mutando o MESMO array a cada evento,
     com dx medido desde o INÍCIO do drag → o delta total era somado de novo a
     cada move (pan acelerava/disparava). Agora cada move aplica o delta total
     a uma CÓPIA do viewport salvo no down.
- `canvas._groupSelector = null` no down/move cancela o retângulo de seleção que
  o Fabric já iniciou nesse mousedown (nosso handler roda depois do interno);
  cursor vira 'grabbing' durante o pan.
- Hints das views 2D atualizados: "Arraste o fundo panorâmica · Shift+arrasto
  seleção múltipla".

## Fix: peça nova "grudava" nas outras e arrastar uma movia outra junto (2026-06-11)
- Sintoma relatado: ao adicionar peça do catálogo ela nascia grudada/sobreposta
  às existentes; ao arrastar uma peça, outra "vinha junto". 3 causas distintas
  (ver obs-009/010/011 em observations.md):
- **Causa 1 (principal) — colisão de uid após loadProject**: `_uid()` é contador
  de sessão (`comp_N`, zera no reload); `saveProject()` grava os uids;
  `loadProject()` (formato novo) restaurava-os sem reposicionar `_uidCounter`.
  Peça nova repetia uid carregado e todos os handlers por uid (object:modified,
  nudge, rotação, delete) passavam a afetar duas peças. Fix: ao carregar projeto
  no formato novo, `_uidCounter` é re-derivado do maior sufixo numérico dos uids
  carregados; comps sem uid ganham um novo.
- **Causa 2 — snap centro-a-centro**: `magneticSnap()` casava 9x9 pontos da
  bounding box incluindo o CENTRO; o par centro-centro fazia a peça arrastada
  saltar para exatamente em cima da vizinha. Fix: ponto central removido de
  `tPoints`/`oPoints` (snap só por bordas/cantos — caso real é encaixar
  treliças ponta a ponta); loops agora usam `.length`.
- **Causa 3 — cascata cega à orientação**: a cascata anti-sobreposição de
  `addComponent()` usava tol=0.05m nos outros eixos (peças levemente
  desalinhadas não eram vistas → nasciam em cima) e media a peça existente
  sempre com `c.length` no eixo de cascata (coluna vertical "ocupava" o
  comprimento inteiro em X). Fix: novo helper local `sizeAlong(c)` usa
  `isQ30AlongZ`/`isQ30Column` para Q30 (coluna/profundidade ocupam só a seção
  0.30m no eixo de cascata); tol subiu para 0.30m (seção transversal do box
  truss) e gap para 0.10m.

## Reaproveitamento de projetos: renomear + salvar = cria cópia (2026-06-11)
- Pedido do usuário: poder reaproveitar projetos — ao alterar o nome de um
  projeto carregado e salvar, criar uma CÓPIA (novo registro) em vez de
  sobrescrever o original.
- Novo estado `savedProjectName` no `trussCalculator()`: guarda o nome do
  projeto no momento do último save/load. Setado em `saveProject()` (sucesso)
  e `loadProject()`; limpo em `deleteProject()` quando o projeto atual é
  excluído.
- `saveProject()`: se `currentProjectId` existe e `projectName` difere de
  `savedProjectName` (comparação com `.trim()`), envia `id: null` no payload →
  backend (`ApiController::salvarProjeto`, que já suportava: POST sem id =
  create, com id = update) cria novo registro. `currentProjectId` passa a
  apontar para a cópia; o original fica intacto. Toast "Cópia Criada" informa
  origem e preservação.
- UX na toolbar: quando o nome difere do salvo, aparece um indicador
  "⎘ salvará como cópia" ao lado do input e o botão muda de "Salvar" para
  "Salvar Cópia".
- Nenhuma mudança de backend foi necessária.

## Vista 3D removida do PDF (2026-06-11)
- Decisão do usuário: mesmo após o fix do `$nextTick`, a imagem 3D continuava
  sem aparecer no PDF ("nao deu certo remova do pdf o 3d").
- Removidos de `exportToPDF()`: a função `capture3D()`, a variável `threeImg`
  e o box "Vista 3D" da Página 1.
- Página 1 agora: cabeçalho → "Resumo Geral" (x=15) e "Ferragens
  (demonstrativo)" (x=140) lado a lado → tabela de quantitativo logo abaixo
  (`tableStartY = Math.max(sumY, hwY) + 8`). Página 2 com as 5 vistas 2D
  permanece igual.
- MANTIDOS (não são código morto): `this._threeRenderer/_threeScene/_threeCamera`
  em `init3DView()` e `preserveDrawingBuffer: true` no WebGLRenderer —
  inofensivos e úteis caso a captura 3D volte no futuro. Confiança: confirmado
  que nada mais os consome hoje, mas seguem a regra de não apagar
  automaticamente.

## Fix: Vista 3D não aparecia na imagem do PDF (2026-06-11)
- Após o fix do hang em `capture3D()`, o PDF passou a gerar normalmente, mas o
  box "Vista 3D" da Página 1 ficava vazio/preto (`threeImg === null`).
- Causa provável: `this.viewMode = '3d'` + `this.init3DView()` eram chamados
  na MESMA tick síncrona do export. O `#canvas3DContainer` usa
  `x-show="viewMode === '3d'"`; se o efeito reativo do Alpine que remove
  `display:none` ainda não tinha sido aplicado quando o renderer WebGL era
  criado/anexado (50ms depois, via setTimeout interno do `init3DView`), o
  `toDataURL()` podia retornar um framebuffer vazio em alguns navegadores
  (canvas WebGL com `display:none` no momento da criação do contexto).
- Fix em `capture3D()` (dentro de `exportToPDF()`):
  - `this.viewMode = '3d'` agora é seguido de `this.$nextTick(() => { this.init3DView(); ... })`,
    garantindo que o Alpine já tenha tornado o container visível antes de
    montar o renderer.
  - Delay interno aumentado de 250ms → 300ms.
  - Antes do `toDataURL`, recalcula `camera.aspect` a partir das dimensões
    reais do canvas, chama `updateProjectionMatrix()`, renderiza, espera um
    `requestAnimationFrame` e renderiza de novo — garante que o framebuffer
    esteja com o frame mais recente antes de capturar.
  - Mantido o `try/catch` em todos os níveis, sempre resolvendo `null` em caso
    de erro (PDF continua sendo gerado mesmo sem a imagem 3D).

## Fix: PDF parou de funcionar após adicionar captura da Vista 3D (2026-06-11)
- `capture3D()` lançava exceção (ex.: `toDataURL` em canvas WebGL "tainted" ou
  `_threeRenderer` indefinido) DENTRO do `setTimeout` de uma `new Promise`,
  fora do `try/catch` do executor — a Promise nunca resolvia/rejeitava e
  `await capture3D()` ficava pendente para sempre, travando todo
  `exportToPDF()` (nenhum download, nenhum erro visível).
- Agora `capture3D()` tem `try/catch` tanto na inicialização quanto dentro do
  `setTimeout`, sempre resolvendo (`null` em caso de erro, logado via
  `console.error`). Todo o bloco de captura está em `try/catch/finally`,
  garantindo que o `viewMode`/zoom/viewport originais sejam restaurados mesmo
  se algo falhar. Se `viewImages` ficar vazio, exibe alerta em vez de travar.
- Validado: `php -l` e `node --check` sem erros.

## Exportação PDF: todas as vistas (3D + 5x 2D) e resumo completo (2026-06-11)
- Novo método `calculateHardwareCounts()` (~L1846): extrai a lógica de
  parafusos/arruelas/porcas que antes só existia dentro de `drawViewHints()`,
  reutilizável por exports.
- `init3DView()` (~L2271): `WebGLRenderer` agora usa `preserveDrawingBuffer:
  true` e guarda referências em `this._threeRenderer/_threeScene/_threeCamera`
  para permitir capturar a cena como imagem fora do loop de animação.
- `exportToPDF()` agora é `async` e gera 2 páginas:
  - Página 1: Vista 3D (capturada via `capture3D()` — alterna `viewMode` para
    `'3d'`, chama `init3DView()`, aguarda 200ms e renderiza/`toDataURL` do
    `renderer.domElement`), "Resumo Geral" (peso, metros lineares/quadrados) e
    "Ferragens (demonstrativo)" (parafusos/arruelas/porcas) ao lado, seguido da
    tabela de Quantitativo & Estoque.
  - Página 2: grid 3x2 com as 5 vistas 2D (Frontal/Fundo/Superior/Lateral
    Esq/Lateral Dir), capturadas alternando `viewMode` + `syncCanvasFrom3D()` +
    `canvas.toDataURL()` (grid oculto durante a captura).
  - Ao final, o `viewMode`/zoom/viewport originais são restaurados.
- Validado: `php -l` e `node --check` sem erros.

## Label 3D do Cubo: "CUBO" em vez de "CON" (2026-06-11)
- `init3DView()` (~L2559): peça `tipo === 'cubo'` agora mostra "CUBO" (antes
  "CON"), seguindo o mesmo padrão de "GREPO"/"SLEEVE" para conectores.
- Validado: `php -l` e `node --check` sem erros.

## Fix: texto do Label cortado no 3D (2026-06-11)
- `init3DView()` (~L2530, branch `tipo === 'label'`): o canvas da textura era
  fixo em 512x128px com fonte 64px — textos longos (ex: "PAINEL ESTRUTURA DO
  QUEIJO") ultrapassavam a largura e ficavam cortados.
- Agora o canvas é dimensionado dinamicamente: `ctx.measureText(text).width`
  + padding define `labelCanvas.width`/`height` (a fonte é reaplicada após o
  resize, pois redimensionar um canvas reseta o contexto 2D). O sprite usa
  `scale.set(spriteHeight * aspectRatio, spriteHeight, 1)` para manter a
  proporção do texto sem distorcer.
- Validado: `php -l` e `node --check` sem erros.

## Sleeve: remover rodízios do 3D, exibir no 2D, label "SLEEVE" no 3D (2026-06-11)
- 3D (`createSleeveModel`, ~L2400): removidos os 4 rodízios adicionados na
  rodada anterior — agora é só `createColoredTruss(0.42, sleeveMat)` (treliça
  roxa maior que o cubo), sem grupo extra/wheels.
- 2D: as 3 views (Frontal/Fundo ~L3013, Superior, Lateral/Lateral Dir) tinham
  `if (comp.tipo === 'sleeve') return;` logo no início do
  `this.components3D.forEach`, pulando o desenho ANTES de chegar no branch
  `else if (comp.tipo === 'sleeve' || comp.tipo === 'sleeve_4faces')` que já
  existia mais abaixo (usado só por `sleeve_4faces`). Removido o early-return
  — `sleeve` (catálogo `SLEEVE`) agora renderiza normalmente em 2D.
- 3D label (`init3DView` ~L2559): peça `tipo === 'sleeve'` agora mostra
  "SLEEVE" (antes "LV"), igual ao padrão "GREPO"/"CON" dos conectores.
- Validado: `php -l` e `node --check` sem erros.

## Fix: não era possível digitar espaço nos campos de texto (ex: nome do Label) (2026-06-11)
- O listener global `window.addEventListener('keydown', ...)` (~L482), criado
  para ativar o modo de pan da câmera com Espaço+arraste, chamava
  `e.preventDefault()` para QUALQUER tecla Espaço pressionada na página,
  inclusive com foco em `<input>`/`<textarea>` — impedia digitar frases com
  espaço (ex: "PAINEL ESTRUTURA DO QUEIJO" no campo de Label).
- Adicionado helper `_isEditableTarget(el)` (INPUT/TEXTAREA/contentEditable);
  o handler agora só ativa `_spaceHeld`/`preventDefault` quando
  `!_isEditableTarget(e.target)`.
- Validado: `php -l` e `node --check` sem erros.

## Sleeve 3D: formato de cubo treliçado roxo e maior, com rodízios (2026-06-11)
- `createSleeveModel()` (~L2397): antes era um `BoxGeometry` simples 0.32m roxo
  ("caixa"). Agora reaproveita `createColoredTruss(length, mat)` (mesma função
  usada pelo cubo) com `sleeveSize = 0.42m` (maior que o cubo de 0.30m) e
  `sleeveMat` (roxo `#a855f7`), pois o sleeve corre por fora da estrutura.
- Adicionados 4 rodízios (`THREE.CylinderGeometry`, material escuro `#1e293b`)
  nos cantos inferiores de um grupo externo que envolve a treliça, simulando o
  carrinho/sleeve que desliza/é puxado pela estrutura.
- Validado: `php -l` e `node --check` sem erros.

## Nova feature: Label de texto nas estruturas (2026-06-11)
- Novo tipo de componente `tipo: 'label'` (`excludeFromQuantitative: true`,
  não entra no quantitativo/BOM nem em metros lineares/quadrados).
- Toolbar (área de Acessórios, visível em todas as views 2D): input de texto
  (`labelText`, padrão "TEXTO") + botão 🏷️ → `addLabel(labelText)` →
  `_createLabel(text, x, y, z)` cria o componente no centro da view atual
  (posição calculada conforme Frontal/Fundo/Superior/Lateral/Lateral Dir).
- Renderização 2D: `fabric.IText` adicionado nos 3 blocos de
  `this.components3D.forEach` (Frontal/Fundo ~L3019, Superior ~L3211,
  Lateral/Lateral Dir ~L3410), com `angle` = `rotationZ`/`rotationY`/`rotationX`
  conforme a view, `data: {uid}`, arrastável (`magneticSnap` no `moving`).
  Evento `editing:exited` grava `obj.text` de volta em `comp.text`,
  `calculateQuantitative()` + `pushHistory()`.
- Renderização 3D (`init3DView`, ~L2496): `THREE.Sprite` com textura de canvas
  (texto amarelo `#fbbf24`, fundo transparente) na posição `(x,y,z)` do label;
  excluído da etiqueta de medida genérica (skip-list em ~L2538 agora inclui
  `lona`/`painel_led`/`label`).
- Duplo-clique: `mouse:dblclick` (~L791) agora retorna cedo para
  `comp.tipo === 'label'`, deixando o IText do Fabric entrar no modo de edição
  nativo (em vez de alternar rotação H/V).
- Validado: `php -l` e `node --check` sem erros.

## Fix: peças novas sobrepondo/grudando nas existentes ao adicionar (2026-06-11)
- `addComponent()` (~L961): a checagem anti-empilhamento antiga só comparava
  posição exata (`tol=0.05`) e cascateava em passos fixos de 0.5m — peças maiores
  que 0.5m (Q30 de 1m, 1.5m, etc.) ainda ficavam sobrepostas/grudadas nas
  vizinhas mesmo após o deslocamento.
- Agora `hasOverlap()` calcula sobreposição real considerando o tamanho
  (`peca.comprimento`/`c.length`, fallback 0.30m) de ambas as peças no eixo de
  cascata (`x` para Frontal/Fundo/Superior, `z` para Lateral/Lateral Dir),
  exigindo `minDist = (newSize+cSize)/2 + gap(0.05)`. O passo de cascata
  (`step = newSize + gap`) também passou a ser proporcional ao tamanho da nova
  peça.
- Validado: `php -l` e `node --check` sem erros.

## Label 3D do Grepo: "GREPO" em vez de "CON" (2026-06-11)
- `init3DView()` (~L2487): label de conector mostrava "CON" para cubo e grepo.
  Agora grepo mostra "GREPO" (cubo continua "CON").
- Validado: `php -l` e `node --check` sem erros.

## Quantitativo & Estoque: Metros Lineares e Metros Quadrados (2026-06-11)
- Novo estado: `totalLinearMeters`, `totalSquareMeters` (junto a `totalWeight`).
- `calculateQuantitative()`: para CADA componente (mesmo os com
  `excludeFromQuantitative`), soma:
  - `totalLinearMeters` += `comp.length || 0.30` para `tipo` em
    `Q30`/`cubo`/`grepo` (metragem total de Box Truss usada).
  - `totalSquareMeters` += `comp.width * comp.height` para `tipo` em
    `lona`/`painel_led` (área de lona/painel a confeccionar).
- UI: 2 novas linhas no painel "Quantitativo & Estoque" abaixo de "Peso Total da
  Estrutura" — "Metros Lineares (Box Truss)" sempre visível, "Metros Quadrados
  (Lona/LED)" só com `x-show="totalSquareMeters > 0"`.
- Exportações: PDF (`exportToPDF`) imprime as 2 metragens abaixo da tabela via
  `doc.lastAutoTable.finalY`; Excel (`exportToExcel`) adiciona linhas
  "Metros Lineares (Box Truss):" e "Metros Quadrados (Lona/Painel LED):" após o
  "Peso Total da Estrutura (kg):".
- Validado: `php -l` e `node --check` sem erros.

## Fix contagem de ferragens: por FACE conectada de cubo, não por cubo (2026-06-11)
- A contagem anterior (`numCubos*4/8/4 + numGrepos*2/4/2`) subestimava: ela
  contava 4/8/4 POR CUBO, mas a regra correta é 4/8/4 POR FACE do cubo que recebe
  um Q30. Ex.: estrutura em "U" (2 cubos de canto, cada um conectando 2 Q30 —
  vertical+horizontal) deve dar 16 parafusos / 32 arruelas / 16 porcas (4 faces
  totais), não 8/16/8 (2 cubos).
- Nova lógica em `drawViewHints()`:
  - Para cada Q30, calcula os 2 pontos de extremidade (`q30Endpoints`) a partir de
    `comp.x/y/z`, `comp.length/2` e direção (`isQ30Column` → eixo Y, `isQ30AlongZ`
    → eixo Z, senão plano X-Y via `comp.rotationZ`).
  - `totalCuboFaces` = nº de extremidades de Q30 dentro de `tol = cuboHalf(0.15) + 0.06`
    do centro de cada cubo (peças Q30 são geradas encurtadas por `cuboHalf` e
    encostadas no cubo).
  - `parafusos = totalCuboFaces*4 + numGrepos*2`, `arruelas = totalCuboFaces*8 +
    numGrepos*4`, `porcas = totalCuboFaces*4 + numGrepos*2`. Grepo continua por
    peça (1 ponto de fixação cada).
- Validado: `php -l` e `node --check` sem erros.

## Duplo-clique alterna Horizontal/Vertical (0°/90°) em vez de somar 45° (2026-06-11)
- `canvas.on('mouse:dblclick', ...)` (~L768): antes incrementava
  `rotationZ/Y/X += 45 % 360` a cada duplo-clique (cicla por 8 ângulos). Agora usa
  `normalize(deg) = ((deg % 180) + 180) % 180` e `toggle(deg) = normalize(deg) === 90 ? 0 : 90`
  — alterna apenas entre Horizontal (0°) e Vertical (90°), as 2 únicas posições
  usadas nas estruturas Q30. Qualquer outro ângulo (ex.: diagonais do Sketch) é
  normalizado para 90° na primeira alternância.
- Atalho de teclado "R" (`rotateSelected(45)`, rotação livre por 45°) NÃO foi
  alterado — continua incrementando livremente, é um recurso separado.
- Validado: `php -l` e `node --check` sem erros.

## Painel de ferragens (parafusos/arruelas/porcas) demonstrativo + fix orientação Grepo 3D (2026-06-11)
- `drawViewHints()` (painel no canto superior esq. das views 2D, mostra eixos +
  dimensão perpendicular): expandido de 178x58 para 200x109px, com novas linhas
  abaixo de uma linha separadora mostrando contagem de ferragens — PURAMENTE
  DEMONSTRATIVO, não entra em `calculateQuantitative()`/quantitativo/BOM:
  - `numCubos = components3D.filter(tipo === 'cubo').length`
  - `numGrepos = components3D.filter(tipo === 'grepo').length`
  - Parafusos = `numCubos*4 + numGrepos*2`
  - Arruelas  = `numCubos*8 + numGrepos*4`
  - Porcas    = `numCubos*4 + numGrepos*2`
  - Premissa do usuário: cada lado conectado de um CUBO usa 4 parafusos/8
    arruelas/4 porcas; cada GREPO usa 2 parafusos/4 arruelas/2 porcas.
- Fix `createGrepoModel()` (3D): o modelo era construído com o comprimento
  (0.30m) ao longo do eixo X ("deitado"). Adicionado `group.rotation.z = Math.PI/2`
  no fim da função para alinhar o comprimento ao eixo Y, mesma convenção de
  `createTrussSection`/cubo (que usa CylinderGeometry ao longo de Y) — agora o
  Grepo aparece "em pé" por padrão e gira corretamente junto com `comp.rotationX/Y/Z`.
- Validado: `php -l` e `node --check` sem erros.

## Acessórios: ferramentas em todas as views 2D + painel de LED com textura (2026-06-11)
- Barra de Acessórios (toolbar abaixo da principal, via `<div class="basis-full">`
  forçando quebra de linha no `flex-wrap`) agora visível em TODAS as views 2D
  (`x-show="viewMode !== '3d'"`, antes só Frontal/Fundo).
- `addAccessory(tipo, w, h)` e `toggleAccessorySketch` (sketch de 2 cliques) agora
  são "view-aware":
  - Novo `_accessoryPointToWorld(px, py, depth)` converte pixel→mundo conforme a
    view ativa: Frontal/Fundo → (x,y); Superior → (x,z); Lateral/Lateral Dir → (z,y).
  - Superior: arrasto define `width` (X) e posição `x`/`z`; `height` mantém o valor
    digitado em `accessoryHeight`; `y = dimensions.height/2`.
  - Lateral/Lateral Dir: arrasto define `height` (Y) e posição `y`/`z`; `width`
    mantém `accessoryWidth`; `x = 0`.
  - `_createAccessory(tipo, width, height, x, y, z=0)` — novo parâmetro `z`.
- 3D: Painel de LED agora usa textura procedural (`createLedPanelTexture()`,
  canvas 256x256 com grid de pixels coloridos, `THREE.CanvasTexture` com
  `RepeatWrapping`, repetição proporcional ao tamanho do painel) aplicada via
  `map`/`emissiveMap` nas faces frontal/traseira do `BoxGeometry`
  (array de 6 materiais: laterais = moldura cinza escura `#1e293b`, frente/trás =
  textura de LED com `emissive` ciano). Lona mantém material translúcido simples.
- Validado: `php -l` e `node --check` sem erros.

## Acessórios visuais: Lona e Painel de LED, sem contar no quantitativo (2026-06-11)
- Novos "tipos" de `components3D`: `lona` e `painel_led` — itens puramente
  ilustrativos (`excludeFromQuantitative: true`, já suportado por
  `calculateQuantitative()`), com campos próprios `width`/`height` (metros) em vez
  de `length`/`catalogId`/`peso`.
- Toolbar (`app/views/home/index.php` ~L99-107), grupo compacto (apenas ícones,
  sem labels de texto — versão verbose inicial quebrava o layout do
  `<div class="...flex flex-wrap gap-3 items-center justify-between">` e foi
  substituída), visível apenas em Frontal/Fundo:
  - Inputs `accessoryWidth`/`accessoryHeight` (`w-11`, padrão 3x2m).
  - Botões "🖼️" / "💡" → `addAccessory(tipo, w, h)` adiciona centralizado na view
    atual com as dimensões digitadas.
  - Botões "🖼️✏️"/"💡✏️" → `toggleAccessorySketch(tipo)`: modo de 2 cliques (estilo
    sketch) onde o 1º clique marca um canto e o 2º marca o canto oposto; `width`/
    `height`/posição central são calculados a partir dos 2 pontos via
    `pixelToMeters`. Esc cancela (`_exitAccessorySketch`). Mutuamente exclusivo com
    o Sketch de estrutura (`toggleSketchMode()`/`switchView()` chamam
    `_exitAccessorySketch()` e vice-versa).
- `_createAccessory(tipo, width, height, x, y)`: cria o componente (`uid`, `codigo`
  `LONA`/`PAINEL_LED`, `nome`, `tipo`, `width`, `height`, `depth` (Lona=0.03m,
  Painel LED=0.1m), `x/y/z`, rotações zeradas, `excludeFromQuantitative: true`),
  faz `push` em `components3D`, `syncCanvasFrom3D()`, `calculateQuantitative()`,
  `pushHistory()` e mostra toast.
- Renderização — agora nas 5 views 2D + 3D (inicialmente só Frontal/Fundo, expandido
  para Superior/Lateral/Lateral Dir): retângulo `fabric.Rect` selecionável/arrastável
  (entra no `object:modified`/`magneticSnap`/`deleteSelected`/`rotateSelected`
  genéricos via `data.uid`, sem código extra). Lona = branco translúcido tracejado;
  Painel LED = escuro com borda ciano tracejada.
  - Frontal/Fundo: `width=comp.width*scale`, `height=comp.height*scale`,
    `angle=comp.rotationZ`.
  - Superior (Planta): `width=comp.width*scale`,
    `height=Math.max((comp.depth||0)*scale, 2)`, `angle=comp.rotationY`.
  - Lateral/Lateral Dir: `width=Math.max((comp.depth||0)*scale, 2)`,
    `height=comp.height*scale`, `angle=comp.rotationX`.
  - 3D (`init3DView`): `THREE.BoxGeometry(width, height, depth)` (era
    `PlaneGeometry` sem profundidade) com `MeshStandardMaterial` translúcida (Lona
    branca opacity 0.55, Painel LED escuro opacity 0.9 com `emissive` ciano), sem
    sprite de etiqueta de medida (`if (comp.tipo === 'lona' || 'painel_led') return;`
    logo após a criação do model).
- Save/load/undo: `components3D` é serializado genericamente via `JSON.stringify`,
  então os novos campos (`width`/`height`/`depth`/`excludeFromQuantitative`) já são
  persistidos sem mudanças no `ApiController`/`ProjectRepository`.
- Validado: `php -l` e `node --check` sem erros.

## Sketch: frame plano, duplo-clique finaliza, reutilizável sem limpar (2026-06-11)
- `generateFromSketch()` (`app/views/home/index.php`): removido o bloco
  `if (depth > 0.5) {...}` que duplicava a estrutura em `z = -depth` (back frame
  espelhado) e gerava vigas conectoras laterais via `addSideConnectingBeams()`.
  Sketch agora gera **somente** o frame plano (`z = 0`), pois `dimensions.width`
  está fixo (sem UI para editar profundidade) e a duplicação automática ficava
  incorreta/indesejada. Removida também a variável `const depth` agora não usada.
- Geração agora é **aditiva**: removido `this.components3D = []` no início de
  `generateFromSketch()`. `virtualStock` é inicializado com o estoque total e depois
  decrementado para cada peça Q30 já presente em `this.components3D` (mesmo padrão
  usado em `addSideConnectingBeams`), evitando estourar estoque ao gerar múltiplas vezes.
- `toggleSketchMode()`: removida a lógica de salvar/limpar (`_savedComponents`,
  `this.components3D = []`) ao entrar no modo Sketch e de restaurar ao sair — a
  estrutura existente permanece visível e intacta durante o Sketch. Isso permite
  reutilizar a ferramenta (desenhar nova linha → gerar → desenhar outra) sem precisar
  limpar ou recriar o projeto.
- Novo handler `mouse:dblclick` (`_sketchDblClickHandler`) registrado/removido junto
  com `_sketchClickHandler`/`_sketchMoveHandler`: duplo-clique chama `finishSketch()`.
- `finishSketch()`: se houver >= 2 pontos, chama `generateFromSketch()` (que agora
  permanece em modo Sketch e só limpa os pontos desenhados) e em seguida sempre chama
  `toggleSketchMode()` para sair do modo Sketch — unificando o fluxo de "finalizar"
  (Enter/Esc/botão "✓ Finalizar"/duplo-clique).
- `clearCanvas()` e `switchView()`: removidas referências remanescentes a
  `_savedComponents` (variável eliminada); `switchView()` também passou a remover o
  handler `mouse:dblclick` e a preview line/label ao trocar de view durante o Sketch.
- `addSideConnectingBeams()` permanece em uso pelo `$watch('dimensions.width', ...)`
  (compatibilidade com projetos antigos que tenham componentes em `z != 0`) — não
  ficou órfã.
- Validado: `php -l` e `node --check` sem erros.

## Snap magnético sempre ativo, removido botão/toggle (2026-06-11)
- Snap já vinha com `snapEnabled: true` por padrão; usuário decidiu deixá-lo sempre ativo,
  sem opção de desligar pela UI.
- Removidos: botão "Snap" da toolbar (`app/views/home/index.php`), estado `snapEnabled`,
  e o guard `if (!this.snapEnabled) return;` em `magneticSnap()` — a função agora sempre
  executa o alinhamento magnético ao mover componentes nas 5 views 2D.
- Validado: `php -l` e `node --check` sem erros.

## Fix definitivo: scroll de "Componentes Disponíveis" dentro do card (2026-06-11)
- O fix anterior (`auto-rows-max content-start`) resolveu o cálculo de scrollHeight, mas
  o `max-h-[350px]` fixo no grid não acompanhava a altura real do card — ainda cortava
  visualmente porque o card crescia/encolhia (coluna esquerda é esticada pela grid de 12
  colunas via `align-items: stretch` para acompanhar a altura da coluna central
  Toolbar+Canvas), e o grid interno não preenchia nem respeitava esse espaço.
- Fix: card "Componentes Disponíveis" (`app/views/home/index.php` ~L13) agora é
  `flex-1 flex flex-col min-h-0`; h2/p ganharam `shrink-0`; o grid interno trocou
  `max-h-[350px]` por `flex-1 min-h-0` (mantendo `overflow-y-auto auto-rows-max
  content-start`). Resultado: o grid ocupa exatamente o espaço restante do card
  (= altura da coluna central) e rola internamente sem cortar itens.
- Validado: `php -l` sem erros.

## Toolbar do canvas: ocultar controles 2D na Vista 3D + Undo/Redo refresca 3D (2026-06-11)
- Análise pedida pelo usuário sobre a toolbar do canvas (Escala, Grade, Snap, Undo/Redo,
  Sketch, Zoom, Remover, Limpar, Salvar, Projetos): todos os botões estavam implementados
  e funcionais, mas a toolbar não era "view-aware" — ficava visível inteira mesmo na
  Vista 3D, onde Escala/Grade/Snap/Zoom/Sketch/Remover operam sobre o canvas Fabric.js 2D
  oculto (no-op silencioso, ou no caso do Sketch/Remover, dava a impressão de bug pois
  nada acontecia visualmente).
- Fix 1: adicionado `x-show="viewMode !== '3d'"` em: seletor de Escala, botão Grade,
  botão Snap, botão Sketch, bloco de Zoom (−/%/+/⟲/⊡) e botão Remover. Undo/Redo,
  Limpar Tudo, Projeto/Salvar/Projetos continuam sempre visíveis (fazem sentido em
  qualquer view).
- Fix 2: `_restoreHistorySnapshot()` (undo/redo, ~L1263) agora chama `this.init3DView()`
  quando `viewMode === '3d'`, além de `syncCanvasFrom3D()`/`calculateQuantitative()` —
  antes, um Ctrl+Z/Ctrl+Y feito na Vista 3D só atualizava o canvas 2D oculto, sem refletir
  na cena Three.js visível até trocar de view e voltar.
- Validado: `php -l` e `node --check` sem erros.

## Fix: lista "Componentes Disponíveis" cortando itens no scroll (2026-06-11)
- Problema relatado: dentro do card "Componentes Disponíveis" (coluna esquerda,
  `app/views/home/index.php`), o grid 2 colunas com `overflow-y-auto max-h-[350px]`
  cortava itens no meio e o scroll não alcançava a última linha (CUBO/GREPO).
- Causa: grid CSS com `align-content: stretch` (padrão) dentro de container com
  `overflow-y-auto` faz as rows esticarem para preencher a área visível, distorcendo
  o cálculo de `scrollHeight` e cortando a última linha.
- Fix: adicionado `auto-rows-max content-start` à classe do grid — as rows passam a
  ter altura natural (não esticam) e ficam alinhadas ao topo, então o scroll captura
  a altura real do conteúdo e mostra todos os itens corretamente.
- Validado: `php -l` sem erros.

## Modelo 3D real do conector GREPO (2026-06-11)
- Referência do usuário: https://www.pedestaltv.com.br/grapple-boxtruss-q30 — o GREPO
  ("Grapple") é um perfil em "U" de aço galvanizado (chassi ~42x30x300mm, furos oblongos
  17x38mm), acabamento metálico prateado, usado para grampear/unir tubos de Q30 sem cubo.
- Antes: GREPO era renderizado em 3D como um simples cubo 0.30x0.30x0.30 vermelho/rosa
  (`#f43f5e`), igual ao CUBO mas com cor diferente.
- Agora: novo `createGrepoModel()` em `app/views/home/index.php` constrói um perfil "U"
  (base + 2 abas laterais via `THREE.BoxGeometry`, comprimento 0.30m, seção 42x30mm,
  parede 4mm) em material metálico prateado (`#cbd5e1`, metalness 0.85), com furos oblongos
  marcados como retângulos escuros nas abas. Usado no lugar do cubo simples no loop de
  renderização 3D (`comp.tipo === 'grepo'`).
- 2D (badges/cores na tabela de peças e no canvas) NÃO foi alterado — continua usando a cor
  rose/red `#f43f5e` como identificador visual no editor 2D, distinto do CUBO (laranja).
- Validado: `php -l` e `node --check` sem erros.

## Remoção definitiva de Sapata do catálogo (2026-06-10)
- Após remover a geração automática de sapatas (item anterior), o usuário pediu para remover
  "sapata" por completo. A tabela `produtos` no banco já não tinha mais SAPATA/SAPATA1/SAPATA2/
  SLEEVE com `tipo='sapata'` (provavelmente removidos manualmente via UI /pecas em algum
  momento) — restam apenas Q30 (7), GREPO, CUBO.
- Removidas as 3 linhas SAPATA/SAPATA1/SAPATA2 de `database/seed.sql` para que um futuro
  re-seed não as reintroduza. SLEEVE permanece no seed (ainda é um tipo válido — peça interna,
  disponível para adição manual).
- `app/views/pecas/index.php` já não tinha 'sapata' nas opções de tipo (filtro/form) nem nos
  badges — nada a remover lá. `app/views/home/index.php` não tem mais nenhuma referência a
  'sapata' como tipo (só um comentário explicativo).
- migration.sql mantém o comentário `-- 'Q30', 'sleeve', 'sapata', etc.` na coluna `tipo` —
  é só um comentário ilustrativo do schema (TEXT livre, sem CHECK constraint), não força
  remoção.

## Remoção da geração de Sapatas/Pé de Galinha (2026-06-10)
- Esclarecimento do usuário sobre os componentes em uso: conectores válidos são apenas
  CUBO e GREPO; SLEEVE é uma peça que corre por DENTRO dos box truss (não é mais usada como
  conector de junta, decisão já tomada antes); SAPATAS (SAPATA/SAPATA1/SAPATA2) não são mais
  usadas — a estrutura gerada via Sketch deve ter apenas a estrutura vertical (colunas Q30 +
  conectores nas juntas), sem nenhuma base alargada.
- Removido de `generateFromSketch()`: o bloco `if (this.sapataType === 'pg1' || 'pg2') {...}`
  que chamava `addPeDeGalinha(q30s, basePoints)` ao final da geração.
- Removida a função `addPeDeGalinha()` inteira (ficou órfã/sem chamadores após a remoção
  acima) e o campo de estado `sapataType: 'pg1'` (só era usado dentro dela).
- Mantido (NÃO removido): a leitura de `comp.quantOverride`/`comp.excludeFromQuantitative`
  em `calculateQuantitative()` — são genéricos e necessários para compatibilidade com
  projetos salvos ANTES desta mudança, que podem ter componentes de sapata com esses campos.
- Catálogo (`produtos`) mantém os itens SAPATA/SAPATA1/SAPATA2 cadastrados (não removidos do
  banco — apenas não são mais gerados automaticamente; usuário pode ainda adicionar
  manualmente do catálogo se quiser).
- Validado: `php -l` e `node --check` sem erros.

## Cascata anti-empilhamento ao adicionar componentes (2026-06-10)
- Bug relatado: ao adicionar mais de uma peça (ex: clicando várias vezes no catálogo), as peças
  ficavam exatamente sobrepostas no centro do canvas (`addComponentToCenter` sempre usa
  `canvas.width/2, canvas.height/2`), parecendo "agrupadas".
- Fix em `addComponent()`: antes de criar `newComp`, se já existir um componente com
  x/y/z idênticos (tolerância 0.05m), desloca a nova peça em 0.5m ao longo do eixo horizontal
  da view atual (X para frontal/fundo/superior, Z para lateral/lateral_dir), repetindo até achar
  posição livre (máx 60 tentativas) — peças adicionadas em sequência ficam em "cascata" visível.
- Validado: `php -l` e `node --check` sem erros.

## Mais escalas, dicas de orientação/profundidade e undo/redo (2026-06-10)
- Pedido do usuário: estruturas grandes precisam de mais escalas, e as views 2D precisam de
  dicas de orientação/profundidade/distância para ficarem mais intuitivas; além disso, uma
  toolbar com desfazer/salvar projeto.
- Escalas: select `canvasScale` (toolbar do canvas) ganhou opções 5/10/15/20/30/50/75/100/150/200
  px=1m (antes só 20/50/100). Combinado com o zoom existente (20%-500%), cobre desde estruturas
  de 30m+ até edição em detalhe.
- Dicas de orientação: novo método `drawViewHints()` desenha um painel fixo no canto superior
  esquerdo de cada view 2D (chamado no fim de `syncCanvasFrom3D()`), com: (1) o eixo horizontal
  da tela e seu nome (Comprimento/Profundidade), (2) o eixo vertical da tela e seu nome
  (Altura/Profundidade), (3) a dimensão do eixo perpendicular à tela (o que "entra"/"sai" da
  tela) com ícone ⊗/⊙ e valor em metros (`this.dimensions.width/height/length`).
- Undo/Redo: pilha linear de snapshots `JSON.stringify(components3D)` em `history`/
  `historyIndex` (máx 50). `pushHistory()` ignora snapshot duplicado consecutivo. `undo()`/
  `redo()` restauram via `_restoreHistorySnapshot()` (com flag `_historySuppress` para evitar
  reentrância), chamando `syncCanvasFrom3D()`+`calculateQuantitative()`. Botões ↶/↷ na toolbar
  (`canUndo()`/`canRedo()` controlam `disabled`) + atalhos globais Ctrl+Z / Ctrl+Y / Ctrl+Shift+Z
  no listener de `keydown` já existente. "Salvar Projeto" já existia (`saveProject()`) — não
  precisou de mudança; `loadProject()` agora chama `resetHistory()` ao carregar (carregar não é
  uma ação desfazível).
- Validado: `php -l` e `node --check` sem erros.

## Sincronização de orientação Q30 entre views 2D/3D (2026-06-10)
- Problema relatado: peças Q30 horizontais/verticais não ficavam consistentes entre as views
  Frontal/Fundo/Superior/Lateral/Lateral Dir/3D — cada view classificava a orientação usando um
  campo de rotação diferente (rotationZ, rotationY ou rotationX) com thresholds 45°-135° próprios,
  e `object:modified` só atualizava UM desses campos por edição, deixando os outros dois "stale".
- Fix: dois helpers únicos `isQ30AlongZ(comp)` e `isQ30Column(comp)` (perto de `syncCanvasFrom3D`)
  passam a ser usados por TODOS os blocos de renderização Q30 (frontal/fundo, superior,
  lateral/lateral_dir) para classificar a peça de forma consistente:
  - "Ao longo de Z" (profundidade): rotationX≈0 e rotationY≈90/270
  - "Coluna" (vertical/Y): não está ao longo de Z e rotationZ≈90/270
  - Caso contrário: peça no plano X-Y, ângulo = rotationZ (pode ser diagonal, ex: Sketch)
- Novo helper `applyQ30Rotation(comp, viewMode, targetAngle)` usado em `object:modified`
  (activeSelection e objeto único), só para `comp.tipo === 'Q30'`:
  - Frontal/Fundo: escreve `rotationZ` livre (preserva ângulos diagonais do Sketch; Fundo espelha
    `180 - angle`), zera `rotationX`/`rotationY`. Corrigido bug adicional: renderização diagonal
    no Fundo agora também usa o espelhamento `180 - rotationZ`.
  - Superior/Lateral/Lateral Dir: faz snap do ângulo resultante para 0°/90° e escreve o trio
    canônico completo (rotationX/Y/Z) — `(0,90,0)` = ao longo de Z, `(0,0,90)` = coluna,
    `(0,0,0)` = horizontal X.
  - Conectores (cubo/grepo/sleeve) NÃO foram alterados — continuam escrevendo diretamente o campo
    de rotação correspondente à view (rotação é só cosmética para cubos).
- Validado: `php -l` e `node --check` sem erros após a mudança.

## Remoção do card "Dimensões da Estrutura" (2026-06-10)
- Removido o card de UI "Dimensões da Estrutura" (Comprimento total, Altura das colunas,
  Largura/Profundidade, Tipo de Sapata/Base) do painel esquerdo.
- DECISÃO TÉCNICA (não removida a lógica dependente, apesar do pedido "remover tudo"):
  `dimensions.length/height/width` e `sapataType` são usados em ~50 pontos do arquivo —
  são a base do sistema de coordenadas de TODAS as views 2D/3D (`floorY`, profundidade Z,
  origem dos eixos), das linhas de cota, da geração via Sketch, do Pé de Galinha e do
  save/load/PDF de projeto. Não são "código morto": são estruturais.
- Mantidos como valores padrão fixos no estado (`dimensions: {length:6, height:3, width:4}`,
  `sapataType: 'pg1'`), inalterados, para que Sketch/render/cotas/Pé de Galinha/save-load
  continuem funcionando exatamente como antes — só a UI de edição foi removida.
- Por que: per CLAUDE.md "nunca apagar código automaticamente; classificar por confiança" —
  remover essa lógica quebraria a renderização inteira (não é uma feature isolada). Se o
  usuário quiser editar essas dimensões novamente no futuro, repor o card ou criar um modal
  de configurações que escreva nos mesmos campos `dimensions`/`sapataType`.

## Remoção do "Pórtico Auto" (2026-06-10)
- Removido o botão "Gerar Pórtico Auto" (card "Dimensões da Estrutura") e a função `generateAutoLayout()`.
- A geração de estrutura passa a ser feita somente via Sketch (`generateFromSketch()`).
- Verificado: `generateTrussLine()`, `addSideConnectingBeams()`, `addPeDeGalinha()` continuam usados por
  `generateFromSketch()` — nenhuma função ficou órfã/morta. `dimensions.*` e `sapataType` continuam em
  uso por outras partes (render 2D/3D, cotas, save/load de projeto, Sketch).

## Correções no quantitativo de peças (estoque) do trussCalculator (2026-06-10)
- Conector de junta agora é CUBO (não SLEEVE): `generateTrussLine()` e `addSideConnectingBeams()`
  inserem um componente CUBO em cada junta entre 2 segmentos Q30 de uma mesma viga/coluna/lateral.
  SLEEVE foi removido de toda a lógica de geração (Auto-Layout/Sketch) por decisão do usuário —
  só é usado quando o usuário adiciona manualmente uma peça SLEEVE pelo catálogo.
- Pé de Galinha (sapataType pg1/pg2) agora é contabilizado no quantitativo como 1x SAPATA1/SAPATA2
  (item de estoque dedicado), via novo método `addPeDeGalinha(q30s, points)`. A renderização 2D/3D
  continua desenhando CUBO + 2x Q30 (visual real da montagem), mas para o quantitativo:
  - o componente CUBO recebe `quantOverride: { codigo, nome, tipo, peso }` apontando para
    SAPATA1/SAPATA2 do catálogo
  - os 2x componentes Q30 (pernas) recebem `excludeFromQuantitative: true`
- `calculateQuantitative()` passou a respeitar `comp.excludeFromQuantitative` e `comp.quantOverride`.
- Removida duplicação: `generateAutoLayout()` e `generateFromSketch()` agora chamam o mesmo
  `addPeDeGalinha()` em vez de blocos de código quase idênticos.
- Removido ~55 linhas de código morto/inalcançável dentro de `canvas.on('object:modified', ...)`:
  ramos `else if` duplicados para `2d_fundo` e `2d_lateral_dir` que nunca executavam (já cobertos
  por condições anteriores no mesmo `if/else if`). Os ramos realmente executados foram validados
  matematicamente (round-trip tela↔3D correto para as 5 vistas 2D).

## Zoom nas vistas 2D (2026-06-09)
- Adicionado zoom com scroll do mouse nas vistas Frontal, Superior e Lateral
- Botões + e − na toolbar com indicador percentual
- Zoom se mantém ao redesenhar o canvas, reseta ao trocar de vista
- Range: 20% a 500%
- Implementado via `canvas.setZoom()` + `canvas.zoomToPoint()` do Fabric.js

## Snap magnético entre componentes (2026-06-09)
- Componentes snapam automaticamente quando arrastados próximos a outros
- Alinha bordas (left/right/top/bottom) e centros entre objetos
- Ajusta snap visual independente do zoom (snapDist = 12 / zoom)
- Botão toggle Snap na toolbar ao lado da Grade
- Ao snapar, objetos companheiros (mesma posição lógica) acompanham
- Fix: `canvas.forEachObject()` → `canvas.getObjects().forEach()` (método correto do Fabric.js)

## Duplicação automática removida no addComponent (2026-06-09)
- `addComponent()` não cria mais cópias espelhadas (frente/trás, lateral direita)
- Agora adiciona exatamente UM componente por clique ou drag

## Sapata 3D visível (2026-06-09)
- Placa elevada para y=0.015 (sobre o chão, não enterrada)
- Espessura aumentada de 0.02m para 0.03m
- Material alterado de baseMat (escuro) para trussMat (prateado)
- Conector elevado proporcionalmente

## openModal/closeModal/showToast expostos globalmente (2026-06-09)
- `window.openModal`, `window.closeModal`, `window.showToast` eram undefined
- Adicionado bind no init() do layout nos dois arquivos: design_system.js e state_manager.js
- Botões Cadastrar/Editar/Excluir em /pecas voltaram a funcionar

## Medidas 3D (2026-06-09)
- Linhas de cota nos eixos X (comprimento), Y (altura) e Z (profundidade)
- Labels em THRE.Sprite com canvas texture (sempre viradas para câmera)
- Etiqueta individual em cada componente: comprimento em Q30, "SL" em sleeve, "SP" em sapata
- Profundidade/Largura liberada para 0 (estruturas 2D tipo gol)
