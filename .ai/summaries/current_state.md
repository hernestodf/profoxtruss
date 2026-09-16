# Estado Atual do Projeto — 2026-08-19

## Últimas Mudanças
- **Redesenho completo do Ctrl+Shift: destravar tudo em vez de reimplementar
  seleção** (sugestão do PRÓPRIO usuário, depois de 3 rodadas de fix na
  seleção retangular própria: "o interessante é ele desabilitar o que
  criamos para modificar somente o que está na visão de cada face... ele
  dará a opção de selecionar e mover os itens"). Ideia certa: em vez de
  tentar reimplementar a seleção retangular do Fabric.js na mão (frágil,
  dependia de detalhes internos de como o Fabric.js liga seus próprios
  listeners de mouse — 3 tentativas anteriores, cada uma corrigindo um
  mecanismo interno diferente que a anterior não tinha pego), a solução
  robusta é DESTRAVAR TUDO temporariamente enquanto Ctrl+Shift estão
  segurados, e deixar o Shift+arrasto/clique NATIVO do Fabric.js (que já
  funciona perfeitamente pra metade não-travada, testado e comprovado)
  simplesmente enxergar toda peça como selecionável nesse momento — zero
  código de seleção próprio necessário. Removida TODA a maquinaria anterior
  (interceptação em fase de captura, `stopImmediatePropagation`, retângulo
  próprio, listeners em `document`, `_finishCtrlShiftSelect`) e substituída
  por, em `app/views/home/index.php`:
  - `_unlockAllForSelection()`: percorre o canvas e destrava (`selectable:
    true, evented:true`) + restaura a opacidade original de TODA peça
    atualmente travada — reusa o `_viewLockBaseOpacity` já existente de
    `_applyViewLocks` (idempotente, sem risco de compor a opacidade em
    ciclos repetidos de destrava/trava — validado isolado em Node.js).
  - `keydown`/`keyup` em `window` rastreiam Ctrl+Shift seguros juntos:
    no keydown (as duas pressionadas, view não-3D, fora de modo Sketch),
    chama `_unlockAllForSelection()` uma vez. No keyup (QUALQUER uma das
    duas solta), NÃO re-trava de propósito — soltar as teclas costuma vir
    logo antes de arrastar a seleção recém-montada pro lugar (ex: juntou 4
    peças, soltou as teclas, vai mover o grupo); re-travar ali bloquearia
    esse arrasto seguinte. O re-travamento correto continua acontecendo
    sozinho via `selection:cleared` (já chama `_applyViewLocks()`) ou no
    próximo resync completo do canvas.
  Resultado: Ctrl+Shift+clique/arrasto agora É o Shift+clique/arrasto
  nativo do Fabric.js (comprovadamente funcional), só que temporariamente
  sem filtro de metade travada — herda de graça toda a robustez nativa
  (clique único, shift+clique pra alternar, arrasto de área, tudo) sem
  nenhuma reimplementação. Deploy feito e confirmado em produção
  (2026-08-19) — ainda sem confirmação visual do usuário (pendente; sessão
  sem acesso a navegador).
- **Fix real (achado lendo o fabric.min.js bundlado): o gesto Ctrl+Shift
  nunca TERMINAVA** (usuário, testando o fix anterior numa Vista Frontal
  com 4 peças coladas: "não funcionou"). Sem acesso a navegador (extensão
  Claude in Chrome não conectada nesta sessão), fui direto ler o código
  fonte do `fabric.min.js` (v5.3.0) bundlado no projeto
  (`public/assets/js/fabric.min.js`) pra entender o mecanismo exato em vez
  de continuar só especulando pelo código do app. Achei: o Fabric.js só
  amarra o listener de `mousemove`/`mouseup` em `document` (o que faz um
  arrasto continuar funcionando mesmo se o cursor sair do canvas, E É o que
  dispara o evento sintético `'mouse:up'` que os handlers via
  `canvas.on('mouse:up', ...)` recebem) de DENTRO do seu próprio
  processamento de `mousedown` nativo do DOM. O fix anterior (fase de
  captura + `stopImmediatePropagation()`) bloqueava exatamente esse
  `mousedown` nativo do Fabric.js pra evitar que ele sequestrasse a seleção
  — efeito colateral: isso bloqueava JUNTO o listener que faria o `'mouse:
  up'` disparar. Resultado: o retângulo roxo aparecia e crescia
  normalmente durante o arrasto (o `'mousemove'` do canvas É amarrado sem
  depender do mousedown), mas soltar o botão nunca disparava nada — a
  seleção nunca terminava, silenciosamente. Corrigido em
  `app/views/home/index.php`: o gesto Ctrl+Shift inteiro (mousedown +
  mousemove + mouseup) agora é seguido inteiramente na mão, com
  `document.addEventListener('mousemove'/'mouseup', ...)` próprios,
  amarrados dentro do próprio handler de mousedown e removidos assim que o
  mouseup dispara — sem depender em nada do relay de eventos internos do
  Fabric.js pra esse gesto específico. A lógica de finalizar a seleção
  (interseção + soma com a seleção anterior) virou uma função própria
  (`_finishCtrlShiftSelect`) chamada direto do `onUp`. **Lição pro
  histórico**: sem acesso a navegador, ler o código-fonte da biblioteca de
  terceiros bundlada (em vez de só o código do app) foi o que resolveu —
  duas rodadas anteriores de fix, ambas plausíveis e bem raciocinadas pelo
  código do app sozinho, não pegaram esse mecanismo interno do Fabric.js.
  Deploy feito e confirmado em produção (2026-08-19) — ainda sem
  confirmação visual do usuário (pendente).
- **Fix definitivo: Ctrl+Shift agora junta peças clicando uma a uma, e nunca
  perde a seleção anterior** (usuário, testando o fix anterior: "são 4
  peças e não tá indo com o ctrl+shift"). O fix anterior (ver entrada
  abaixo) resolveu o clique inicial cair em cima de peça destravada, mas
  não resolveu dois problemas mais profundos, ambos ligados à ORDEM dos
  eventos do Fabric.js:
  (1) O listener NATIVO do Fabric.js pra clique/seleção roda no
  `mousedown` de verdade do DOM, ANTES do evento customizado `'mouse:down'`
  que os handlers via `canvas.on()` recebem — então clicar numa peça
  destravada trocava a seleção ativa POR CONTA PRÓPRIA do Fabric.js antes
  de qualquer código deste app rodar, silenciosamente jogando fora
  qualquer seleção Ctrl+Shift já montada em cliques anteriores (por isso
  "4 peças" nunca se acumulavam — cada clique novo apagava os anteriores).
  Corrigido: o Ctrl+Shift agora é pego num listener na FASE DE CAPTURA do
  DOM (`canvas.upperCanvasEl.addEventListener('mousedown', ..., true)`)
  com `stopImmediatePropagation()` — o Fabric.js nunca chega a processar
  esse clique como seleção normal, o app tem controle total desde o
  início.
  (2) O retângulo de seleção próprio (ver entrada anterior) só fazia algo
  se `width > 2 || height > 2` — um CLIQUE simples (sem arrastar de
  verdade, o jeito mais natural de escolher peças espalhadas uma a uma)
  virava um retângulo de tamanho ~0 e não selecionava nada. Corrigido: o
  mesmo teste de interseção retângulo-vs-peça agora roda pra QUALQUER
  tamanho de retângulo — um clique puro vira naturalmente um teste "esse
  ponto cai em cima de qual peça".
  Resultado combinado: cada clique (ou arrasto) com Ctrl+Shift agora
  ACRESCENTA à seleção que já existia (lida de `canvas.getActiveObject()`
  no fim do gesto, que agora só é alterado pelo próprio código deste app —
  nunca mais pelo Fabric.js por baixo dos panos) em vez de substituí-la —
  dá pra juntar peças espalhadas, uma clicada de cada vez, mesmo que
  estejam travadas. Validado isolado em Node.js: 4 cliques sequenciais em
  4 peças diferentes acumulam corretamente (`[p1]→[p1,p2]→[p1,p2,p3]→
  [p1,p2,p3,p4]`), e clicar de novo numa peça já selecionada não duplica.
  Deploy feito e confirmado em produção (2026-08-19).
- **Fix: Ctrl+Shift+arrasto não iniciava em cima de peça já destravada**
  (usuário: "veja o projeto teste... não deixa pegar os objetos na visão
  planta cima"). Investigado com os dados REAIS do projeto salvo "teste"
  (id=16, banco de produção, 8 peças) em vez de suposição — achei que:
  (1) o projeto inteiro tem Z=0 em TODAS as peças (estrutura totalmente
  rasa nesse eixo), então na Planta Cima/Baixo (eixo Y) o corte fica
  relevante — mas o ponto médio calculado por `_axisMidpoint` (média entre
  a travessa de baixo, y=-0,22, e a de cima, y=0,73) cai a menos de 0,5mm
  ACIMA da altura exata dos 4 montantes (y=0,2545), por uma pequena
  assimetria real na montagem — resultado, rodando o `_isLockedForView`
  REAL extraído do arquivo contra os dados reais: 6 das 8 peças ficam
  travadas na Planta Cima (só as 2 travessas de cima continuam
  clicáveis), bem mais restritivo do que uma estimativa a olho sugeriria.
  (2) O gesto Ctrl+Shift+arrasto (ver entrada anterior) exigia começar o
  clique em espaço vazio (`!opt.target`) — igual ao Shift sozinho. Com
  quase tudo travado (não-evented, "invisível" pro hit-test do Fabric.js),
  a maior parte da tela já contava como "vazia", MAS se o clique inicial
  caísse bem em cima de uma das 2 peças que sobraram destravadas — bem
  provável, já que é natural começar o arrasto perto de uma peça visível
  —, o Fabric.js já selecionava/começava a arrastar SÓ ela sozinha antes
  do meu handler rodar, e o Ctrl+Shift nunca chegava a iniciar. Corrigido
  em `app/views/home/index.php`: Ctrl+Shift agora NÃO exige mais começar
  em espaço vazio — cancela explicitamente (`canvas.discardActiveObject()`
  + `canvas._currentTransform = null` + `canvas._groupSelector = null`)
  qualquer seleção/arrasto de objeto único que o Fabric.js já tenha
  iniciado sozinho nesse mesmo mousedown antes de prosseguir com a seleção
  retangular própria. Resolve não só este projeto, mas qualquer layout
  denso onde sobra pouco espaço "puro" vazio pra começar o gesto. Deploy
  feito e confirmado em produção (2026-08-19).
- **Copiar/colar (Ctrl+C/V) respeita seleção Ctrl+Shift em qualquer view**
  (pedido: "ctrl+shift mais seleção copiar e colar independente da visão
  pega todos elementos"). `copySelected()` já funcionava certo com uma
  seleção Ctrl+Shift (lê `.data.uid` de cada objeto da ActiveSelection sem
  checar `selectable`/`evented`, então já copiava peças travadas
  normalmente). O gap estava em `pasteClipboard()`: depois de colar, as
  cópias novas passam por `syncCanvasFrom3D()` (que já roda
  `_applyViewLocks()` no final) — se o deslocamento em escada do paste
  empurrar alguma cópia pro lado TRAVADO da view atual (comum quando o
  original já estava perto do corte), essa cópia nascia com
  `selectable:false, evented:false`, e a seleção final do grupo colado
  (montada na mão via `fabric.ActiveSelection`, então ainda "parecia"
  selecionada) não conseguia ser arrastada junto com o resto — quebrando a
  expectativa de "colar pega tudo, não importa a view". Corrigido:
  `pasteClipboard()` agora destrava (`selectable:true, evented:true`)
  qualquer cópia que tenha nascido travada, ANTES de montar a
  ActiveSelection final — mesmo padrão defensivo já usado no
  Ctrl+Shift+arrasto (ver entrada anterior); `_applyViewLocks()` re-trava
  sozinho depois, ao desmarcar a seleção. Deploy feito e confirmado em
  produção (2026-08-19).
- **Ctrl+Shift+arrasto seleciona também as peças travadas** (pedido: "é
  possível selecionar com Ctrl+Shift e selecionar até as partes do fundo, e
  se selecionar somente com Shift só dá parte que está trabalhando"). Depois
  do travamento de seleção por metade (ver entrada anterior), Shift+arrasto
  sozinho JÁ só pega a metade atual — de graça, porque a seleção retangular
  nativa do Fabric.js pula qualquer objeto `selectable:false`. Faltava um
  jeito de alcançar a metade travada quando o usuário realmente precisa (ex:
  apagar ou mover algo do fundo sem trocar de view). Implementado em
  `app/views/home/index.php`:
  - `_applyViewLocks()`: o loop de travamento que já rodava só dentro de
    `syncCanvasFrom3D()` virou um método próprio, reutilizável — guarda a
    opacidade-base de cada peça em `_viewLockBaseOpacity` na PRIMEIRA vez
    que trava (nunca recaptura de uma opacidade já esmaecida), pra um ciclo
    de destrava→trava não ir multiplicando 0.3× de novo a cada vez
    (validado isolado em Node.js: opacidade depois de um ciclo completo bate
    exata, não composta).
  - Novo gesto Ctrl+Shift+arrasto no fundo do canvas: desenha seu PRÓPRIO
    retângulo de seleção (não usa o rubber-band nativo do Fabric.js, que
    ignoraria as travadas de qualquer forma) e, ao soltar, testa
    interseção manualmente contra TODO objeto no canvas (travado ou não).
    As peças travadas que caíram na área ganham `selectable:true,
    evented:true` temporariamente e entram numa `fabric.ActiveSelection`
    construída na mão — dá pra mover/apagar/rotacionar o grupo
    normalmente (essas ações já trabalham em cima de `canvas.
    getActiveObject()`, não checam o estado de trava). Ao desmarcar a
    seleção (`selection:cleared`), `_applyViewLocks()` roda nesse handler
    e re-trava tudo sozinho — fecha a janela de "vazamento" pra quem só
    selecionou sem mover nada.
  - Dicas de teclado na tela (Frontal/Fundo, Lateral, Planta) atualizadas
    pra mencionar os dois gestos lado a lado.
  - Deploy feito e confirmado em produção (2026-08-19).
- **"Fechar laço" — ajuste automático pra fechar um U/retângulo com peça de
  comprimento fixo** (pedido: "tenho um U já criado... não tá entrando a
  outra peça... é possível as outras formas que não estão encaixando elas
  abrem um pouco pra encaixar... criando um snap perfeito de retângulos e
  quadrados"). Cenário real: duas peças formando os "braços" de um U (ex: 2
  montantes) + uma peça de fechamento (ex: travessa) sendo arrastada pra
  ligar as duas pontas — como peças de catálogo têm comprimento FIXO (não
  esticam), se o vão real entre os braços não bate exatamente com o
  comprimento da peça de fechamento, o sistema de colisão (correto,
  "corpo não pode entrar em outro corpo") bloqueava a inserção sem dar
  outra saída. Decisão explícita do usuário sobre o alcance: o ajuste
  automático só pode mexer NAS DUAS peças que tocam diretamente o vão (os
  2 braços), nunca cascatear pra mais nada conectado a elas — mais
  simples, previsível e seguro. Implementado em `app/views/home/index.php`:
  - `_worldLengthAxis(comp)`: eixo do MUNDO (x/y/z) ao longo do qual o
    comprimento de uma peça-barra corre depois da rotação (reaproveita a
    mesma composição de rotação R=Rz·Ry·Rx de `_worldAABB`) — só retorna
    eixo pra peças com comprimento claramente dominante (≥3x as outras
    dimensões) E rotação alinhada a um eixo do mundo (múltiplo de 90°);
    cubo/sapata/ângulos quebrados retornam null (fora de escopo).
  - `_closeLoopIfBridging(comp, excludeUids)`: acha as duas pontas da peça
    arrastada no mundo; procura, pra CADA ponta, a peça mais próxima (raio
    de captura 30cm — um gesto de "aproximar pra fechar", não toque
    preciso) cujo eixo de comprimento seja DIFERENTE do da peça arrastada
    (perpendicular — descarta "continuação reta" no mesmo eixo, que já é
    resolvida pelo fechar-folga de uma ponta só já existente). Se achou
    duas vizinhas DISTINTAS, calcula o vão real FACE A FACE entre elas
    (via `_worldAABB`, não centro-a-centro — importa pra peças de espessura
    diferente) e compara com o comprimento fixo da peça arrastada: se a
    diferença for pequena (≤15cm — ~2,5x o teto de tolerância de toque já
    usado no resto do app, um ajuste fino de imprecisão de montagem, não
    licença pra deformar a estrutura pra caber qualquer peça errada),
    desliza as duas vizinhas (metade do ajuste cada, em direções opostas)
    até o vão bater exatamente, e encosta a peça arrastada bem no meio,
    flush nas duas pontas. Rede de segurança: se mover qualquer uma das
    duas vizinhas causar uma colisão NOVA com outra peça de fora do laço,
    desfaz tudo e não mexe em nada (evita criar sobreposição em outro
    lugar da estrutura). Chamado em `object:modified`, só pra arrasto de
    peça única, ANTES do bloqueio de colisão/slide-to-limit existente (pra
    resolver o vão antes desse bloqueio barrar o gesto à toa).
  - Validado com o código REAL extraído do arquivo (não uma reimplementação
    à parte) rodado isolado em Node.js, 5 casos: (1) vão 5cm menor que a
    peça — ajusta os dois braços e fecha exato; (2) vão já perfeito — não
    mexe em nada; (3) peça claramente errada (diferença >15cm) — não mexe,
    deixa a colisão normal decidir; (4) continuação reta no mesmo eixo —
    não ativa (não é um laço); (5) ajuste colidiria com uma peça externa —
    desfaz tudo, nada muda. Todos os 5 corretos antes do deploy.
  - Deploy feito e confirmado em produção (2026-08-18).
- **Travamento de seleção por metade da estrutura, por view 2D** (pedido:
  "se estou na visao da direita so posso mexer na direita travando os itens
  da esquerda... vamos criar a visao planta parte de cima e planta parte de
  baixo, resolvendo o problema de seleção incorreta"). Problema: cada view 2D
  projeta a estrutura 3D colapsando um dos 3 eixos do mundo — Frontal/Fundo
  colapsam Z (profundidade), Lateral Esq/Dir colapsam X (comprimento),
  Superior/Planta colapsava Y (altura) — então peças de lados opostos do eixo
  colapsado podiam cair no mesmo ponto de tela, e clicar ali podia
  selecionar/arrastar a peça errada (a que está "atrás", sobreposta na
  tela). Resolvido com duas peças novas em `app/views/home/index.php`:
  - `_axisMidpoint(axis)`: extensão real (mín/máx/meio) das peças JÁ
    COLOCADAS no projeto nesse eixo — dinâmico, não usa `dimensions.length/
    height/width` nominais do card do projeto (decisão explícita do
    usuário: peças raramente ocupam exatamente essas dimensões, o corte tem
    que refletir onde as peças REALMENTE estão).
  - `_isLockedForView(comp)`: mapeia `viewMode` (+ `plantaSide` pro caso da
    Planta) pro eixo/lado que fica editável nesta view, usando a mesma
    convenção de eixos do resto da calculadora (X: leftX=-L/2, rightX=L/2;
    Z: frontZ=0, backZ=-D) — Frontal=Z alto, Fundo=Z baixo, Lateral
    Esq=X baixo, Lateral Dir=X alto, Planta Cima=Y alto, Planta Baixo=Y
    baixo. Se a extensão real do eixo for menor que 5cm (projeto raso/plano,
    sem ambiguidade de fato), NADA é travado — travar tudo num projeto
    plano só atrapalharia sem resolver problema nenhum. Validado
    isoladamente em Node.js: 6 configurações de view contra um layout
    multi-eixo (6 peças em 3 eixos diferentes) todas corretas, mais os 2
    casos-limite (projeto plano, projeto de 1 peça só) sem travar nada.
  - `syncCanvasFrom3D()` ganhou um passo final único (pós-processo, não
    duplicado em cada um dos ~15 branches de tipo de peça): toda peça
    travada na view atual vira `selectable:false, evented:false` (não
    recebe clique/arrasto/drag-select em grupo do Fabric.js — e como
    `evented:false` desliga os eventos do Fabric, os handlers `moving`/
    `object:modified`/`magneticSnap` simplesmente não disparam nela) e
    `opacity *= 0.3` (decisão explícita do usuário: continua visível como
    referência espacial, só não clicável — não some da tela). A colisão 3D
    real (`collidesWithOthers`) não usa Fabric, então continua funcionando
    normalmente contra peças travadas (não dá pra arrastar uma peça editável
    PRA DENTRO de uma travada, só não dá pra selecionar a travada
    diretamente).
  - **Planta Cima/Planta Baixo**: a view "Vista Superior" antiga virou dois
    botões nesta mesma view — decidiu-se NÃO criar um `viewMode` novo pra
    isso (evitaria duplicar ~29 pontos do código que checam
    `viewMode === '2d_superior'` pra mapeamento de tela/rotação, que é
    IDÊNTICO entre cima e baixo). Em vez disso: novo estado `plantaSide`
    ('cima'|'baixo', default 'cima') + novo método `switchViewPlanta(side)`
    que seta `plantaSide` e chama `switchView('2d_superior')` normalmente —
    os botões ficam visualmente independentes (`:class` checa `viewMode
    === '2d_superior' && plantaSide === 'cima'/'baixo'`), mas o `viewMode`
    em si nunca sai do conjunto de 6 valores que já existiam, então TODO o
    código de mapeamento/rotação/PDF/drag pré-existente continuou
    funcionando sem nenhuma alteração.
  - Deploy feito e confirmado em produção (2026-08-18).
- **Guias de alinhamento por eixo independente ("smart guides")** no snap
  magnético 2D (pedido: "ao encostar na coordenada x y z se for alinhada a
  outra devera marcar uma linha... é possível?"). Já existia uma linha-guia
  (`_showSnapGuide`) mas só aparecia quando um PONTO inteiro (X e Y da mesma
  borda/canto de outra peça) ficava perto o bastante ao mesmo tempo — uma
  peça alinhada só em X (com Y bem longe) não recebia feedback nenhum.
  Reescrito `magneticSnap()` (`app/views/home/index.php`) pra resolver X e Y
  de forma INDEPENDENTE: cada eixo busca o candidato mais próximo (entre
  borda-esquerda/centro/borda-direita pra X, topo/centro/base pra Y) entre
  todos os outros objetos, com o mesmo `snapPx` de tolerância de antes — CADA
  eixo pode alinhar sozinho, os dois juntos (canto, comportamento antigo
  preservado), ou nenhum. `_showSnapGuide(point)` atualizado pra aceitar
  `point.x`/`point.y` nulos independentemente e usar `visible:` por linha —
  só a(s) linha(s) do(s) eixo(s) realmente alinhado(s) aparece(m), sem linha
  "falsa" grudada em 0. Cada view 2D mostra 2 dos 3 eixos do mundo por vez
  (trocar de view cobre o terceiro). Validado isoladamente em Node.js: só-X,
  só-Y, nenhum, e ambos — todos os 4 casos corretos antes do deploy. Deploy
  feito e confirmado em produção (2026-08-18). Extensão pra view 3D
  (Three.js) ficou fora de escopo por decisão do usuário nesta rodada.
- **Vistas Isométricas 3D adicionadas ao PDF** (pedido: "coloque as visoes
  das iso no pdf"). `exportToPDF()` (`app/views/home/index.php`) já
  capturava as 5 vistas 2D (Frontal/Fundo/Superior/Lateral Esq/Dir) mas não
  incluía nenhuma vista 3D. Adicionado `captureIsoViews()`: entra
  temporariamente em `viewMode='3d'`, chama `init3DView()` e espera
  `this._threeRenderer` existir (a cena é montada dentro de um
  `setTimeout(0)`, então precisa de polling curto), depois usa
  `setIsometricView(0..3)` (as 4 vistas de canto já existentes nos botões
  "↗ Iso 1".."↙ Iso 4") e captura `renderer.domElement.toDataURL()` a cada
  uma — com `waitFrames(3)` entre a troca de câmera e a captura, porque o
  loop de render (`animate()`/`requestAnimationFrame`) só desenha a nova
  posição no frame seguinte, então capturar sem esperar pegaria a câmera
  antiga ou um frame em branco. Funciona porque o `WebGLRenderer` já era
  criado com `preserveDrawingBuffer: true` (necessário pro `toDataURL()`
  funcionar, já existia antes por outro motivo). Nova "Página 3" do PDF
  (grid 2x2) com as 4 imagens. Restauração do estado original do canvas ao
  final não mudou (reaproveita o `finally` já existente). Deploy feito e
  confirmado em produção (2026-08-18).
- **Confirmado pelo usuário ("ficou perfeito")**: fix da margem de tolerância
  de colisão em `_worldAABB` (`app/views/home/index.php`). Usuário reportou,
  após todos os fixes anteriores (grouping-tolerance, stroke-bleed):
  "continua um quarto para dentro da peca agora no 2d e tambem no 3d" — ou
  seja, sobreposição real de dados (não só visual), confirmada nas duas
  views. Causa raiz: `_worldAABB(comp, margin=0.008)` encolhia a caixa de
  colisão de cada peça em `min(margin, h*0.4)` por face — pra uma peça PM5
  (meia-largura 0.025m), o teto de 40% permitia encolher até 0.008m em cada
  lado, ou seja, até 1,6cm (32% da largura da peça) de sobreposição REAL
  entre duas peças passava batido por `collidesWithOthers` sem nunca ser
  detectada como colisão — batendo com "um quarto" relatado. Corrigido:
  `margin` default de 0.008→0.002 (2mm, suficiente só pra ruído de
  ponto-flutuante/arredondamento) e teto de `h*0.4`→`h*0.2`. Validado
  isoladamente em Node.js antes do deploy: toque real entre peças continua
  permitido (sem falso-bloqueio, testado com Q30+Cubo e dois PM5
  encostados), mas o teto de sobreposição silenciosa caiu de 16mm pra 4mm, e
  uma sobreposição real de 5mm agora é corretamente detectada (antes não
  seria). Não afeta `_touchesExactly`/slide-to-limit/gap-close, que já usam
  margem própria (0, exata) e independem deste valor. Deploy feito e
  confirmado em produção (2026-08-18).
- Fix de renderização 2D (usuário: "3D está ideal, 2D não" — depois de
  todos os fixes de colisão/dados 3D já estarem corretos). Causa:
  `createFabricQ30Object` desenhava o contorno das peças com `strokeWidth:
  2` SEM compensar que o Fabric.js (como qualquer canvas/SVG) desenha o
  contorno CENTRALIZADO na borda (metade pra fora do retângulo nominal).
  Num Q30 (30cm, dezenas de px na tela) isso é irrelevante; num Perfil PM5
  (5cm, só ~3px na tela) os 2px de contorno quase DOBRAVAM o tamanho visual
  — duas peças PM5 corretamente encostadas nos dados 3D apareciam
  visualmente sobrepostas no 2D, mesmo com a colisão/posição real correta
  (por isso "3D ideal, 2D não": o 3D usa geometria real sem esse conceito de
  contorno). Fix: subtrai `STROKE_W` (2px) da largura/altura nominal antes
  de criar o `fabric.Rect`, nos 2 branches de `createFabricQ30Object` (peça
  "longa" com label e peça normal) — o resultado final (preenchimento +
  contorno) bate com o tamanho real pretendido em vez de "tamanho real +
  contorno por fora". Afeta TODOS os tipos que passam por essa função (Q30,
  plana, braco, sapata, montante, travessa) — mais perceptível no PM5 por
  ser a peça mais fina do catálogo (2026-08-18).
- **Bug crítico de colisão corrigido** (usuário: "uma peça está entrando na
  outra... colisão não está correto o cálculo", reproduzido na Vista
  Superior com Perfil PM5). Causa raiz: `object:modified` e o `object:moving`
  (sincronismo visual durante o arrasto) usam uma heurística de "peças na
  mesma posição" pra mover peças coincidentes juntas (ex: Q30 + conector no
  mesmo ponto) — tolerância era 0.05m (5cm), que é a LARGURA INTEIRA de uma
  peça Perfil PM5 (seção 5cm). Duas peças PM5 genuinamente vizinhas (só
  encostadas, não empilhadas) caíam dentro desse raio e eram tratadas como
  "a mesma peça se movendo junto" (`movedUids`) — e como
  `collidesWithOthers(comp, movedUids)` EXCLUI o grupo movido da checagem de
  colisão, isso deixava uma peça atravessar a outra completamente, sem
  nenhuma barreira (não era só folga — a peça "entrava dentro" mesmo).
  Corrigido: tolerância apertada de 0.05m pra 0.01m (1cm) nos 8 pontos do
  código (3 no `object:modified` + 5 no `object:moving`, cobrindo as 5
  views 2D) — 1cm fica bem abaixo do menor lado de qualquer peça do
  catálogo (Grepo, 3cm de altura), então só agrupa peças GENUINAMENTE
  coincidentes, não vizinhos encostados. Fix vale pra TODOS os tipos de
  peça, não só PM5 — pedido explícito do usuário ("faça isso pra todas as
  peças... todos os objetos") (2026-08-18).
- Fix real encontrado por simulação com dados reais (pedido: "refaz o balcão
  pra testar de novo"). Sem acesso a navegador, simulei o algoritmo de
  "fechar folga" em Node.js contra as 14 peças reais do `BALCAO COM
  TESTEIRA`, usando "direção até o centro do vizinho mais próximo" como
  proxy da direção de arrasto — resultado: só 8/14 fechavam. Investigando o
  motivo, achei que o "fechar folga" (`object:modified`) usava a DIREÇÃO DO
  ARRASTO (vetor pré→alvo do mouse) pra decidir em que direção procurar uma
  peça pra encostar — isso funciona bem se o usuário arrasta bem na direção
  do vão, mas falha quando a peça vizinha é grande/descentrada e o vão real
  fica num eixo diferente do "centro a centro". Reescrito pra usar a direção
  GEOMÉTRICA exata do vão (novo `_nearestGap(comp, excludeUids)`: acha a
  peça mais próxima e o vetor por eixo — 0 se já sobrepõe naquele eixo,
  senão a distância exata pra fechar — não depende de nenhuma direção de
  mouse). Resimulado com a correção: 12/14 fecham automaticamente (as 2
  restantes ficam bem na borda da tolerância — 40,0mm exatos contra teto de
  40mm, arredondamento). `object:modified` agora usa só a posição final da
  peça (não mais o par pré/alvo) pra decidir a direção do fechamento
  (2026-08-18).
- Recalibração do raio de captura com DADOS REAIS (pedido: "testa de novo
  com o balcão"). Achei o projeto salvo do usuário (`BALCAO COM TESTEIRA`,
  id=15, 14 peças) no banco e rodei a matemática de colisão corrigida em
  cada par próximo: 28 pares Montante/Travessa com folga real de arrasto
  manual entre 11,9mm e 56,2mm (mediana 35,8mm). IMPORTANTE: esse projeto
  foi salvo às 15:04 (hora local), ANTES dos fixes de "fechar folga" (15:35)
  e tolerância proporcional (15:46) — não é uma falha dos fixes, é dado de
  ANTES deles existirem. Mas serviu de calibração real: o piso de 1cm que eu
  tinha chutado só cobriria 2/28 casos reais — o erro humano de mouse fica
  na faixa de 2-5cm, não milímetros. `_touchToleranceFor` recalibrado: piso
  4cm (cobre ~70% dos casos reais de primeira), teto 6cm (cobre 100% da
  amostra real) — antes era piso 1cm/teto 5cm. Ainda proporcional (menor
  dimensão da peça × 60%), só os limites mudaram pra bater com a
  imprecisão real observada, não uma suposição (2026-08-18).
- Snap/"fechar folga" tornados PROPORCIONAIS ao tamanho real de cada peça —
  usuário apontou que trabalhamos com peças de tamanho predefinido, então o
  raio de captura deveria respeitar isso em vez de um valor fixo pra
  qualquer peça. Novo `_touchToleranceFor(comp)`: usa a MENOR dimensão da
  peça (`Math.min(...half)` de `_pieceHalfExtents`) × 60%, com piso de 1cm e
  teto de 5cm. Resultado por tipo: Q30/Cubo/Sleeve ficam no teto de 5cm
  (peças grandes, igual comportamento de antes); Montante/Travessa/Braço/
  Treliça Plana caem pra 1,5cm; Sapata ~1,1cm; Grepo (o menor) vai pro piso
  de 1cm. Aplicado em 2 lugares: (1) `GAP_CLOSE` do "fechar folga" em
  `object:modified` — antes fixo em 5cm, que era 100% da largura de um
  Perfil PM5 (5cm de seção); (2) `magneticSnap`: `snapPx = Math.min(12,
  _touchToleranceFor(comp) * canvasScale)` — pega o MENOR entre os 12px
  fixos (piso de precisão do mouse) e o raio proporcional convertido pra
  pixels na escala atual, evitando que peças pequenas "pulem" uma distância
  enorme relativa ao próprio tamanho ao tentar encaixar (2026-08-18).
- Recurso novo: "fechar folga" (ímã de toque) em `object:modified`, arrasto
  de peça única. Usuário reportou que o problema piorava ao aumentar a
  escala/zoom — diagnóstico: o sistema de colisão só agia como PAREDE
  (impede sobrepor), nunca como ÍMÃ (não puxava a peça pra encostar quando
  soltava com uma folga pequena sem chegar a sobrepor nada). Uma folga real
  de poucos cm é imperceptível em zoom baixo (menos de 1px na tela) mas fica
  bem visível em zoom alto (mesma distância real, mais pixels) — por isso
  "piorava" ao ampliar, mesmo sendo sempre a mesma distância física. Fix:
  quando o drop NÃO colide, estica a MESMA direção do gesto até um raio
  pequeno e FIXO EM METROS (`GAP_CLOSE = 0.05`, 5cm — não pixels, por isso
  funciona igual em qualquer zoom) procurando uma peça pertinho; se achar,
  busca binária (24 iterações) até o ponto de toque exato. Nova função
  `_touchesExactly(comp, excludeUids)` — igual `collidesWithOthers` mas com
  `margin=0` (sem a tolerância de 8mm usada na colisão normal): usar a
  margem tolerante aqui fazia o "fechar folga" convergir ~1,6cm ALÉM do
  toque real (sobrepondo de leve em vez de só encostar) — corrigido com
  margem zero, validado isoladamente (converge a exatamente x=1.000000).
  Escopo: só arrasto de peça única (seleção múltipla mantém a formação
  relativa, não tenta fechar folga individual por peça) (2026-08-18).
- Reanálise completa do sistema de colisão/tamanhos (pedido explícito do
  usuário: "não invente medidas, use medidas exatas") — comparei os 3
  sistemas independentes que precisam bater (colisão `_pieceHalfExtents`,
  geometria 3D `create*Model`, silhueta 2D) peça por peça. Achados e
  corrigidos 2 bugs REAIS de tamanho, ambos em conectores:
  - **Sleeve**: `createSleeveModel()` = `createTrussSection(0.42)` — MESMA
    função do Q30/Cubo (seção 0,30x0,30 fixa), só que 0,42m no eixo do
    comprimento. NÃO é um cubo uniforme de 0,42m. Colisão estava
    `[0.21,0.21,0.21]` (cubo), corrigida pra `[0.21,0.15,0.15]` (comprimento
    x seção real).2D também usava `(22/50)*scale` (0,44m "olhômetro") em vez
    do valor real 0,42m — corrigido nas 3 views (2D: linhas ~4485/4691/4944).
  - **Grepo**: perfil "U" de aço, chassi real 300x42x30mm (constantes
    `length=0.30`, `channelWidth=0.042`, `channelHeight=0.030` já existiam
    no código, só não eram usadas na colisão). Colisão estava
    `[0.15,0.15,0.15]` (tratado como Cubo inteiro) — ~7x maior que o chassi
    real nos eixos de altura/largura. Corrigida pra `[0.015,0.15,0.021]`.
    2D continua usando o ícone de 0,30m (mesmo do Cubo) — decisão consciente
    de MANTER assim por legibilidade/clique (chassi real de 30-42mm seria
    minúsculo pra clicar); só a colisão (física) foi corrigida.
  - **Bug de rotação relacionado** (achado ao investigar o Sleeve): o
    dispatch 3D do Sleeve e do Grepo usava `model.rotation.z = ...`
    (sobrescrevendo) em vez de `model.rotation.z += ...` (acumulando) como
    TODOS os outros tipos — como `createSleeveModel`/`createGrepoModel` já
    nascem com uma rotação de base própria (deitar/levantar o modelo), a
    atribuição direta apagava essa base e desalinhava a peça renderizada.
    Corrigido pra `+=` nos dois.
  - Demais tipos conferidos e CONFIRMADOS consistentes entre os 3 sistemas
    (sem mudança): Q30, Cubo, Sapata, Braço, Treliça Plana, Montante,
    Travessa — todos já batiam exatamente.
- Fix de precisão na busca binária do "deslizar até o limite" (colisão):
  usuário reportou que encostar no 2D ainda deixava espaçamento visível no
  3D. Causa: a busca tinha só 8 iterações, e a precisão é PROPORCIONAL à
  distância total do gesto (cada iteração divide o intervalo por 2), não um
  valor fixo em metros — um arrasto de alguns metros (comum ao trazer peça
  nova até a estrutura) sobrava ~1cm de folga antes de detectar a colisão.
  Subido pra 24 iterações: mesmo um arrasto de 7,7m converge a 0,00025mm de
  erro (testado isoladamente), custo desprezível (roda só uma vez ao
  soltar). A margem de tolerância de toque (`_worldAABB`) não era a causa —
  ela permite leve sobreposição, não gera folga (2026-08-18).
- Fix importante de UX na colisão (usuário reportou "peça não encosta uma na
  outra, parece que dobra de tamanho"): a MATEMÁTICA da caixa de colisão
  estava correta (validada com testes isolados), mas a REAÇÃO à colisão em
  `object:modified` (arrastar/girar) desfazia o gesto INTEIRO ao detectar
  qualquer sobreposição — um excesso de 1mm ao tentar encostar duas peças
  cancelava o arrasto todo e voltava pro ponto de partida, tornando
  praticamente impossível encostar peças na mão (qualquer overshoot mínimo
  = teleporte de volta ao início, não um recuo pequeno). Trocado por busca
  binária (8 iterações) que desliza a peça pela MESMA trajetória do gesto
  até a maior fração do deslocamento que ainda não sobrepõe nada — como uma
  parede física que você encosta e para. Rotação não interpola (vai direto
  pro alvo final, só a posição desliza); se mesmo assim colidir na fração
  mínima (caso de rotação pura causando a sobreposição), reverte posição E
  rotação por completo, igual ao comportamento antigo. Validado com teste
  isolado: peça arrastada de x=3 até x=0,3 (bem dentro de outra peça em
  x=0) parou em x≈0,985, a 1,4cm do ponto de encaixe teórico exato
  (2026-08-18).
- Correção de dado: `MONT-120` (Montante 1.20m) estava errado — o comprimento
  real é 2.20m. Renomeado pra `MONT-220`/"Montante 2.20m" (código passou a
  bater com o padrão de nomenclatura, que codifica o comprimento em cm — como
  `MONT-100`/`MONT-250`/`MONT-300`), `comprimento` corrigido de 1.20 pra 2.20.
  Aplicado no banco local, produção (via UPDATE direto, preservando `id`/
  `estoque`/`cor`) e `seed.sql`. Peças já colocadas em projetos salvos ANTES
  da correção mantêm o comprimento antigo (1.20) — cada instância copia o
  valor no momento de adicionar, não referencia o catálogo dinamicamente
  (2026-08-18).
- 2 fixes de UX reportados pelo usuário testando um projeto real ("balcão",
  peças copiadas/coladas não grudavam e colisão "não parecia funcionar"):
  (1) `magneticSnap`: tolerância era `0.1 * scale` (proporcional ao zoom —
  só 5px na escala padrão 50px/m, praticamente inutilizável em zoom baixo).
  Trocado pra fixo `snapPx = 12` (pixels de tela, independente de zoom),
  igual à maioria das ferramentas de desenho 2D. (2) `pasteClipboard()`
  (usada por colar E por duplicar/Ctrl+D, que só chama copySelected +
  pasteClipboard) empurrava direto pro array sem NENHUMA checagem — por
  isso colar sobre uma estrutura cheia sobrepunha peças e "colisão não
  funcionava" (na real nunca rodava nesse caminho). Agora cascateia o
  deslocamento (mesmo padrão do `addComponent`) até achar posição livre pro
  grupo colado inteiro, testado contra `collidesWithOthers`; se não achar em
  20 tentativas, cola mesmo assim mas avisa via toast (colar em lote não
  deveria travar tudo por 1 peça problemática, diferente de adicionar 1
  peça só). Sketch/Auto-Layout continuam fora do escopo de colisão
  (2026-08-18).
- Card "Propriedades" movido da coluna esquerda (abaixo do catálogo) pra
  coluna CENTRAL, logo abaixo do "Canvas Board Card" (o card com toolbar de
  views + canvas + rodapé de resolução) — pedido específico do usuário.
  Layout reajustado de vertical-estreito (col-span-3) pra horizontal (grid
  2/4 colunas + linha X/Y/Z lado a lado), já que a coluna central é bem mais
  larga. Mesma lógica/estado por trás (`selectedComp`, `xInput`/
  `heightInput`/`zInput`, `applyAxisInput`/`nudgeSelectedAxis`/
  `applyAllAxesInput`) — só mudou posição e layout do HTML (2026-08-18).
- Novo card "Propriedades" na coluna esquerda (abaixo do catálogo de
  Componentes Disponíveis), `x-show="selectedComp"` — pedido do usuário
  ("quadro na esquerda com propriedades"). Mostra nome/código/comprimento/
  peso/cor da peça selecionada + o painel X/Y/Z (campo + ▲/▼ + Aplicar) que
  antes vivia espalhado na toolbar do canvas — migrado pra cá, não duplicado
  (a toolbar agora só tem o botão Raio-X, que é modo de visualização da
  view, não propriedade de peça). Reaproveita os mesmos
  `xInput/heightInput/zInput/selectedComp/applyAxisInput/nudgeSelectedAxis/
  applyAllAxesInput` de antes, só mudou ONDE o HTML aparece (2026-08-18).
- Painel de posição estendido de só Y (altura) pra X/Y/Z completo — usuário
  pediu X e Z também, já que só a altura ficava editável por número, e X/Z
  continuavam dependendo só do arrasto (mesmo em views onde o eixo nem
  aparece pra arrastar, ex: X na Lateral). Refatorado `applyHeightInput`/
  `nudgeSelectedHeight`/`_setSelectedHeight` (métodos fixos em Y) pros
  genéricos `applyAxisInput(axis, inputProp)`/`nudgeSelectedAxis(axis,
  inputProp, delta)`/`_setSelectedAxis(axis, inputProp, val)` — funcionam
  pros 3 eixos, só a trava de SAPATA (sempre no solo) continua específica de
  'y'. Botão "Aplicar" agora é `applyAllAxesInput()`: aplica X/Y/Z juntos
  como UMA posição final com UMA checagem de colisão (em vez de 3 chamadas
  independentes, que fariam 3 reconstruções de canvas). Campos X/Z sempre
  visíveis quando há seleção; campo Y some pra SAPATA (2026-08-18).
- Fix: painel de altura (Y) "parava de funcionar" depois do 1º clique em
  ▲/▼/Aplicar. Causa: `_setSelectedHeight()` chama `syncCanvasFrom3D()`, que
  faz `canvas.clear()` + recria TODOS os objetos Fabric do zero — isso
  derruba a seleção ativa do Fabric.js (o objeto antigo nem existe mais no
  canvas). Como o painel só aparece com `selectedComp` truthy, a peça
  "desselecionava" a cada clique e o painel sumia. Fix: novo
  `_reselectComp(uid)` chamado ao final de `_setSelectedHeight()` — encontra
  o objeto Fabric recém-recriado pelo mesmo `data.uid` e chama
  `canvas.setActiveObject()` nele. Mesma classe de bug provavelmente afeta
  outros fluxos que chamam `syncCanvasFrom3D()` com algo selecionado (não
  investigado/corrigido além do escopo reportado) (2026-08-18).
- 4 recursos novos de controle de posição/altura nos modos 2D (usuário
  reportou falta de "sensação de onde o objeto vai ficar" ao arrastar):
  (1) Indicador de posição ao vivo durante o arrasto — label Fabric.Text que
  segue o cursor mostrando as 2 coordenadas world relevantes à view atual
  (X/Y em Frontal-Fundo, X/Z em Superior, Z/Y em Lateral), criado/atualizado
  dentro de `magneticSnap()` (que já roda em todo evento `moving`), removido
  em `object:modified`. (2) Linha-guia tracejada quando o snap magnético
  gruda de verdade — `magneticSnap` agora guarda o ponto (`bestPoint`) que
  causou o snap e desenha 2 `fabric.Line` (cruz) nele via `_showSnapGuide`;
  antes o snap era "invisível", só sentido depois de soltar. (3) Campo
  numérico "Altura (Y)" + botão Aplicar pra peça única selecionada — nova
  seleção reativa (`selectedCompUid`, populada via `canvas.on('selection:
  created/updated/cleared')`, getter `selectedComp`) e `heightInput`
  sincronizado por `$watch`. (4) Botões ▲/▼ de ajuste fino (5cm). Os 3
  últimos passam por `_setSelectedHeight()`, que roda a MESMA checagem de
  colisão real do arrasto (`collidesWithOthers`) antes de commitar, e
  bloqueia edição de Y pra SAPATA (sempre no solo, Y não editável por
  design). Painel escondido pra sapata (`x-show="selectedComp.tipo !==
  'sapata'"`). Todos os 4 recursos ficaram só nos modos 2D — não existe
  "arrastar com sensação de altura" na Vista 3D (lá já é órbita livre)
  (2026-08-18).
- Raio-X estendido pros 5 modos 2D (mesmo `xrayMode`/`toggleXray()` da vista
  3D, botão 👻 replicado na toolbar 2D). "Iso" NÃO foi replicado em 2D —
  decisão consciente: Frontal/Fundo/Superior/Lateral/Lateral Dir são
  projeções ortogonais fixas (sem câmera livre), então "vista isométrica" não
  tem equivalente ali. `toggleXray()` agora ramifica por `viewMode`: em 3D
  chama `_applyXray` (mexe direto nos materiais da cena persistente); em 2D
  chama `syncCanvasFrom3D()` (o canvas Fabric.js é reconstruído do zero a
  cada sync, e os 3 blocos de finalização de objeto — Frontal/Fundo,
  Superior, Lateral — agora setam `opacity: 0.45` no objeto quando
  `xrayMode` está ativo, antes de `canvas.add(obj)`) (2026-08-18).
- Vista 3D: 2 recursos novos pra lidar com peças escondidas atrás de outras
  (usuário reportou dificuldade de ver itens numa estrutura densa). "Raio-X"
  (`toggleXray`/`_applyXray`, botão 👻 na toolbar 3D): torna todo THREE.Mesh
  da cena semi-transparente (opacity 0.35), preservando a opacidade original
  em `material._xrayOrigOpacity` pra restaurar ao desligar; como Sprites
  (labels) e Line (cotas) não são Mesh, ficam de fora automaticamente e
  continuam legíveis. Precisa ser reaplicado a cada entrada na vista 3D
  (`init3DView` reconstrói a cena do zero — materiais novos, sem estado
  antigo) — feito logo antes do render loop. "Vistas isométricas rápidas"
  (`setIsometricView(0-3)`, botões Iso 1-4): pula a câmera pra um dos 4
  cantos diagonais da estrutura (az 45/135/225/315°, elevação 32°), distância
  proporcional à diagonal da caixa delimitadora (`dimensions.length/height/
  width`) — funciona em qualquer tamanho de projeto. Precisou expor
  `this._threeControls` (antes só variável local do closure de
  `init3DView`), já que mudar `camera.position` sem também mover
  `controls.target` e chamar `controls.update()` não gruda (OrbitControls
  recalcula a posição a partir do target no próximo frame) (2026-08-18).
- Perfil PM5 (montante/travessa): 3D trocado de prisma liso pra seção
  transversal cruciforme REAL (cavidade central maior + 4 câmaras de canto,
  ligadas por nervuras ~2,5mm), via `THREE.ExtrudeGeometry` com um
  `THREE.Shape` de 5 furos (1 central + 4 cantos) sobre o contorno 50x50mm —
  usuário apontou que o 3D anterior (caixa simples) não batia com `pm5.jpeg`.
  Reverte a decisão anterior de "simplificado" tomada ao adicionar a peça.
  Base de orientação (Z da extrusão -> Y no montante, -> X na travessa) fica
  no MESH filho, não no group, porque o dispatch de render sobrescreve
  `model.rotation.x/y` com `=` a cada redesenho (só `.z` usa `+=`). Validado
  fora do browser: volume real da malha bate exatamente com o volume teórico
  com os 5 furos subtraídos (script no histórico da sessão, não commitado).
  Colisão (`_pieceHalfExtents`) não mudou — continua usando o envelope
  externo 50x50mm, que é o mesmo de antes (2026-08-18).
- Colisão real entre peças na calculadora (`app/views/home/index.php`):
  substitui a cascata heurística antiga de `addComponent` (obs-011, falhava
  com peças rotacionadas) por checagem de caixa orientada → AABB no mundo,
  com margem de 2cm (peças "encostadas" não contam como colisão, só
  sobreposição real). Novos helpers: `_pieceHalfExtents`, `_pieceRotationRad`,
  `_worldAABB`, `_aabbOverlap`, `collidesWithOthers`. Aplicado em: adicionar
  (bloqueia com toast se não achar espaço em 60 tentativas), arrastar
  (reverte a peça/grupo pro estado anterior via snapshot se colidir ao
  soltar), girar (alça de rotação E duplo-clique — este último também ganhou
  a trava de orientação de montante/travessa que faltava). Sketch/geração
  automática, copiar/colar e duplicar NÃO passam por `addComponent` e
  portanto ficaram fora do escopo (decisão explícita, não confirmado pelo
  usuário) (2026-08-18).
- Nova família de peças "Perfil PM5" (montante/travessa): perfil de alumínio
  extrudado 50x50mm cruciforme (ver `pm5.jpeg` + PDF de referência, na raiz do
  projeto), distinto do Box Truss Q30. Dois tipos com restrição de orientação
  rígida ditada pelo usuário: `montante` NUNCA deita (sempre vertical, ao
  longo de Y) e `travessa` NUNCA empina (sempre no plano horizontal X-Z).
  Cor é atributo por item de catálogo (varia por comprimento, não por tipo) e
  tinge o render real 2D/3D — nova coluna `produtos.cor` (TEXT, hex).
  Catálogo seed: TRAV-045/095/195 (azul/verde/vermelho, 700/700/400 un) e
  MONT-100/120/250/300 (laranja/marrom/roxo/cinza, 400/180/270/80 un); peso e
  preço ficaram 0.00 (não informados) — editáveis em Peças & Estoque.
  3D simplificado como prisma colorido (mesmo nível de detalhe do Braço, não
  a seção cruciforme real — decisão do usuário). Sem conector próprio: as
  peças se encontram diretamente (sem cubo/grepo). Nova métrica "Metros
  Lineares (Perfil PM5)" separada de "Metros Lineares (Box Truss)" no
  quantitativo/PDF/Excel, para não misturar as duas famílias de perfil sob o
  mesmo rótulo (2026-08-17). Ver `.ai/context/business_rules.md`.
- Nova peça SAPATA: base de apoio retangular sólida (4 faces), 3 modelos
  (30×50, 30×60, 30×80), altura fixa 3.5cm, constraint de solo (Y=0), aço
  escovado no 3D, renderização 2D nas 5 views, label "SP" no catálogo.
  `addComponent` força `compY = 0.0175`; `object:modified` e `nudgeObject`
  reforçam. Quantitativo inclui metros lineares (2026-06-15).
  Ver `.ai/memory/improvements.md` e `.ai/context/business_rules.md`.
- Lona com imagem PNG/JPG: botões 📷/📷🚫 na toolbar aplicam/removem imagem
  nas lonas selecionadas (`applyLonaImage`/`removeLonaImage`); dataURL máx.
  1024px salvo em `comp.image` (vai no projeto); 2D = `fabric.Pattern` na
  frontal/fundo via `_getLonaImage` + `lonaImgCache` (closure); 3D = textura
  no material (2026-06-11). Ver `.ai/memory/improvements.md`.
- Lona com cor configurável: color picker na toolbar (`accessoryColor`),
  `comp.color` aplicado no 2D (`_hexToRgba` p/ manter transparência) e 3D;
  `applyAccessoryColor()` recolore lonas selecionadas; fallback mantém visual
  antigo em projetos salvos (2026-06-11). Ver `.ai/memory/improvements.md`.
- Copiar/Colar/Duplicar: Ctrl+C/Ctrl+V/Ctrl+D nas views 2D, seleção única ou
  múltipla, qualquer tipo de peça; cola com offset 0,5m em escadinha e deixa
  as cópias selecionadas (`copySelected`/`pasteClipboard`/`_clipboard`)
  (2026-06-11). Ver `.ai/memory/improvements.md`.
- Fix: duplo-clique nas views Lateral/Lateral Dir não alternava horizontal↔
  vertical em peças lineares (Q30/plana/braco). O handler alternava `rotationX`
  (inócuo — a classificação usa rotationY/Z via isQ30AlongZ/isQ30Column);
  agora calcula o ângulo atual na view e usa `applyQ30Rotation` com o toggle
  0↔90 (trio canônico). Frontal também corrigida p/ peças alongZ (2026-06-11).
  Ver `.ai/memory/improvements.md`.
- Nova peça "Braço" (tipo `braco`, BRACO-300, 3m, 1,5kg/R$120) CORRIGIDA pelo
  usuário: é 1 BARRA estrutural linear (1 tubo, sem faces, não é treliça).
  Taxonomia oficial em `.ai/context/business_rules.md`: box_truss faces:4 /
  trelica_plana faces:2 / braco faces:1 (complexidade: Box > Plana > Braço).
  3D = `createBracoModel` (cilindro único teal r=0.025m); 2D = fino
  (~0.05m) em todas as direções transversais nas 5 views. Movimentação/rotação
  igual Q30 (2026-06-11). Ver `.ai/memory/improvements.md`.
- Nova peça estrutural "Treliça Plana" (tipo `plana`, PLANA-200/250/300 no
  banco + seed): 2 faces ligadas por diagonais, seção 0,30m x ~0,05m, sem
  volume fechado. Comporta-se como Q30 (rotação canônica, cascata, metros
  lineares), cor lime; 2D mostra espessura fina nas vistas de ponta/planta;
  3D via `createFlatTrussSection` (2 banzos + zigue-zague). Regras de montagem
  do usuário (cubo = sólido 0,30m, conexão na face externa, sem interseção,
  dimensão final = peça + cubo) registradas em `.ai/context/business_rules.md`
  e glossário (2026-06-11). Ver `.ai/memory/improvements.md`.
- Novo acessório "PAR LED" (botão 🔦, tipo `'parled'`, 0.25×0.30×0.25m fixo,
  fora do quantitativo): 2D = `_createParLed2D` (corpo + lente âmbar, 5 views);
  3D = `createParLedModel` procedural (cilindro + lente emissiva + garra).
  Modelo Sketchfab citado pelo usuário não foi usado (exigiria GLB baixado +
  GLTFLoader; projeto é offline-first) (2026-06-11). Ver
  `.ai/memory/improvements.md`.
- Pan por clique-e-arrasto no fundo das views 2D (sem tecla): arrasto em área
  vazia move a vista; Shift+arrasto mantém a seleção múltipla; desativado nos
  modos Sketch/Acessório. De quebra, 2 bugs do pan antigo corrigidos: condição
  `opt.e.target === null` nunca era verdadeira (Espaço+arrasto não funcionava)
  e o `mouse:move` acumulava o delta total a cada evento (pan "disparava")
  (2026-06-11). Ver `.ai/memory/improvements.md`.
- Fix triplo "peça gruda / arrastar uma move outra": (1) `loadProject` agora
  re-deriva `_uidCounter` dos uids carregados (antes peça nova repetia uid de
  peça carregada e os handlers por uid moviam/deletavam duas peças juntas);
  (2) `magneticSnap` sem o ponto central (snap centro-a-centro sobrepunha
  peças totalmente); (3) cascata anti-sobreposição de `addComponent` agora é
  ciente de orientação via `sizeAlong()` (`isQ30AlongZ`/`isQ30Column`), com
  tol=0.30m e gap=0.10m (2026-06-11). Ver `.ai/memory/improvements.md` e
  obs-009/010/011.
- Reaproveitamento de projetos: renomear um projeto carregado e salvar agora
  cria uma CÓPIA (novo registro), preservando o original. Novo estado
  `savedProjectName`; `saveProject()` envia `id: null` quando o nome mudou;
  indicador "salvará como cópia" + botão "Salvar Cópia" na toolbar. Sem
  mudanças de backend (2026-06-11). Ver `.ai/memory/improvements.md`.
- Vista 3D REMOVIDA do PDF (decisão do usuário — a captura WebGL seguia
  falhando). `exportToPDF()` agora gera: Página 1 = cabeçalho + Resumo Geral +
  Ferragens (lado a lado) + tabela de quantitativo; Página 2 = 5 vistas 2D.
  `capture3D()`/`threeImg` removidos; refs `_threeRenderer/_threeScene/
  _threeCamera` e `preserveDrawingBuffer` mantidos em `init3DView()` para uso
  futuro (2026-06-11). Ver `.ai/memory/improvements.md`.
- Fix: imagem da Vista 3D não aparecia no PDF (box vazio/preto na Página 1).
  `capture3D()` agora aguarda `this.$nextTick()` após `viewMode = '3d'` antes
  de chamar `init3DView()` (garante que `#canvas3DContainer` deixou de estar
  `display:none` antes do WebGL renderizar), aumenta o delay para 300ms e
  recalcula `camera.aspect`/renderiza 2x (incluindo um `requestAnimationFrame`)
  antes do `toDataURL` (2026-06-11). Ver `.ai/memory/improvements.md`.
- Fix: PDF travava (sem download, sem erro) porque `capture3D()` podia lançar
  exceção dentro do `setTimeout` de uma Promise, deixando `await` pendente
  para sempre. Agora tem `try/catch` completo e sempre resolve (2026-06-11).
  Ver `.ai/memory/improvements.md`.
- PDF agora traz Vista 3D + grid das 5 vistas 2D + Resumo Geral (peso, m
  lineares/quadrados) + Ferragens (parafusos/arruelas/porcas), via novo
  `calculateHardwareCounts()` e captura do `THREE.WebGLRenderer`
  (`preserveDrawingBuffer: true`) (2026-06-11). Ver `.ai/memory/improvements.md`.
- Label 3D do conector CUBO agora mostra "CUBO" (era "CON") (2026-06-11). Ver
  `.ai/memory/improvements.md`.
- Fix: texto do Label no 3D era cortado em frases longas (canvas fixo
  512x128px). Agora o canvas/sprite é dimensionado conforme o tamanho real do
  texto (`measureText` + padding) (2026-06-11). Ver `.ai/memory/improvements.md`.
- Sleeve: removidos os rodízios do modelo 3D (volta a ser só treliça roxa
  maior); 2D agora renderiza `tipo === 'sleeve'` (antes era pulado por
  early-return nas 3 views); label 3D agora mostra "SLEEVE" (era "LV")
  (2026-06-11). Ver `.ai/memory/improvements.md`.
- Fix: listener global de Espaço (pan da câmera) bloqueava digitação de espaço
  em qualquer `<input>`/`<textarea>` (ex: texto do Label). Agora ignora
  campos editáveis (2026-06-11). Ver `.ai/memory/improvements.md`.
- Sleeve 3D agora tem o mesmo formato de cubo treliçado do conector CUBO,
  porém roxo (`sleeveMat`) e maior (0.42m vs 0.30m), com 4 rodízios na base
  (`createSleeveModel` reaproveitando `createColoredTruss`) (2026-06-11). Ver
  `.ai/memory/improvements.md`.
- Nova feature "Label" (texto livre, ex: nome/marca da estrutura): toolbar com
  input + botão 🏷️ (`addLabel`/`_createLabel`), `tipo: 'label'`
  (`excludeFromQuantitative: true`), renderizado como `fabric.IText` editável
  (duplo-clique) nas 5 views 2D e como `THREE.Sprite` de texto no 3D
  (2026-06-11). Ver `.ai/memory/improvements.md`.
- Fix: ao adicionar peças repetidamente, a cascata anti-sobreposição agora
  considera o tamanho real da peça (não só posição exata + passo fixo de 0.5m),
  evitando que peças grandes fiquem grudadas/sobrepostas (2026-06-11). Ver
  `.ai/memory/improvements.md`.
- Quantitativo & Estoque agora exibe "Metros Lineares (Box Truss)" (soma de
  Q30+cubo+grepo) e "Metros Quadrados (Lona/LED)" (soma width*height de
  lona/painel_led), também incluídos nas exportações PDF/Excel (2026-06-11). Ver
  `.ai/memory/improvements.md`.
- Fix contagem de ferragens demonstrativa: agora é 4 parafusos/8 arruelas/4 porcas
  POR FACE de cubo conectada a um Q30 (calculado por proximidade geométrica das
  pontas dos Q30 ao centro de cada cubo), não mais por cubo inteiro. Ex.:
  estrutura em "U" (2 cubos de canto, 4 faces) = 16/32/16 (2026-06-11). Ver
  `.ai/memory/improvements.md`.
- Duplo-clique numa peça nas views 2D agora alterna apenas Horizontal (0°) ↔
  Vertical (90°), em vez de somar 45° por clique (2026-06-11). Ver
  `.ai/memory/improvements.md`.
- Painel de ferragens demonstrativo (parafusos/arruelas/porcas) no canto superior
  esq. das views 2D (`drawViewHints`), calculado a partir da contagem de
  cubo/grepo em `components3D` — não entra no quantitativo. Fix: Grepo 3D estava
  "deitado", agora "em pé" (`createGrepoModel` com `rotation.z = Math.PI/2`)
  (2026-06-11). Ver `.ai/memory/improvements.md`.
- Acessórios: barra de ferramentas e sketch agora "view-aware" em todas as 5 views
  2D (`_accessoryPointToWorld`), e Painel de LED tem textura de LED procedural no
  3D (`createLedPanelTexture`, BoxGeometry com 6 materiais) (2026-06-11). Ver
  `.ai/memory/improvements.md`.
- Acessórios visuais "Lona" e "Painel de LED" (2026-06-11): novos tipos de
  componente puramente ilustrativos (`excludeFromQuantitative: true`, não entram
  no quantitativo), com `width`/`height`/`depth` em metros (Lona=0.03m,
  Painel LED=0.1m). Adicionados via toolbar compacta (Frontal/Fundo): digitando
  WxH (`addAccessory`) ou desenhando 2 cantos (`toggleAccessorySketch` →
  `_createAccessory`). Renderizados nas 5 views 2D (Frontal/Fundo/Superior/
  Lateral/Lateral Dir) como retângulo `fabric.Rect` e como `THREE.BoxGeometry`
  (com profundidade) translúcido no 3D; arrastáveis/deletáveis/rotacionáveis via
  mecanismos genéricos existentes (uid). Ver `.ai/memory/improvements.md`.
- Sketch reformulado (2026-06-11): gera apenas o frame plano (sem back frame em
  `z = -depth` nem vigas conectoras laterais — bloco `if (depth > 0.5)` removido de
  `generateFromSketch()`); geração agora é aditiva (`components3D` não é mais zerado,
  `virtualStock` desconta peças Q30 já existentes); duplo-clique no canvas finaliza a
  linha atual (`_sketchDblClickHandler` → `finishSketch()`); modo Sketch não limpa/
  restaura `components3D` ao entrar/sair (`_savedComponents` removido), permitindo
  desenhar, gerar e desenhar novamente sem limpar o projeto. Ver `.ai/memory/improvements.md`.
- Snap magnético sempre ativo, sem botão de toggle (2026-06-11).
- Toolbar do canvas: controles 2D-only ocultos na Vista 3D; Undo/Redo refresca a cena 3D (2026-06-11).
- Sapatas/Pé de Galinha removidas da geração via Sketch (2026-06-10): estrutura gerada agora é
  só a estrutura vertical (Q30 + conectores CUBO/GREPO nas juntas), sem base alargada. Removida
  função `addPeDeGalinha()` e estado `sapataType` (órfãos). Conectores válidos: CUBO e GREPO.
  SLEEVE = peça interna ao box truss, não usada como conector. Catálogo SAPATA/SAPATA1/SAPATA2
  mantido no banco (não gerado automaticamente, mas disponível para adição manual).
- Usabilidade do canvas 2D (2026-06-10):
  - Mais opções de escala no select `canvasScale`: 5/10/15/20/30/50/75/100/150/200 px = 1m
    (antes só 20/50/100), para cobrir estruturas muito grandes ou edição em detalhe.
  - Novo painel `drawViewHints()` no canto superior esquerdo de cada view 2D (frontal/fundo/
    superior/lateral/lateral_dir): mostra os 2 eixos representados na tela e a dimensão do
    eixo perpendicular (profundidade/altura/comprimento conforme a view), chamado a partir de
    `syncCanvasFrom3D()`.
  - Undo/Redo geral: pilha de snapshots JSON de `components3D` (`history`/`historyIndex`,
    máx 50 estados) via `pushHistory()`/`undo()`/`redo()`/`resetHistory()`. Botões ↶/↷ na
    toolbar + atalhos Ctrl+Z / Ctrl+Y (ou Ctrl+Shift+Z). `pushHistory()` chamado após:
    object:modified, addComponent, deleteSelected, rotateSelected, duplo-clique de rotação,
    nudge por seta, finishSketch (geração via Sketch) e clearCanvas. `loadProject()` chama
    `resetHistory()` (carregar projeto não é "desfazível").
- Sincronização de orientação Q30 entre as 5 views 2D (frontal/fundo/superior/lateral/lateral_dir) e 3D:
  novos helpers `isQ30AlongZ(comp)` e `isQ30Column(comp)` são agora a ÚNICA fonte de verdade para
  classificar uma peça Q30 como "ao longo de Z" (profundidade), "coluna" (vertical/Y) ou "no plano X-Y"
  (horizontal/diagonal). Os 3 blocos de renderização (frontal/fundo, superior, lateral/lateral_dir)
  usam esses helpers. `object:modified` agora chama `applyQ30Rotation(comp, viewMode, target.angle)`
  para peças Q30 (ativeSelection e objeto único): em Frontal/Fundo escreve `rotationZ` livre
  (preserva diagonais do Sketch, fundo espelhado `180 - angle`), zerando `rotationX/rotationY`;
  em Superior/Lateral/Lateral Dir faz snap do ângulo para 0°/90° e escreve o trio canônico completo
  (rotationX/Y/Z). Conectores (cubo/grepo/sleeve) mantêm o comportamento antigo (escrita direta do
  campo de rotação correspondente à view).
- Quantitativo: CUBO agora é o conector gerado/contado nas juntas entre segmentos Q30 (generateTrussLine, addSideConnectingBeams) — SLEEVE removido da geração automática (Auto-Layout/Sketch) por decisão do usuário
- Removido o card/botão "Gerar Pórtico Auto" e a função `generateAutoLayout()` (decisão do usuário, 2026-06-10).
  Geração de estrutura agora é feita exclusivamente via Sketch (`generateFromSketch()`), que reaproveita
  `generateTrussLine`, `addSideConnectingBeams` e `addPeDeGalinha` — nenhuma dessas funções ficou órfã.
  `dimensions.length/height/width` e `sapataType` continuam em uso (Sketch, render 2D/3D, cotas, save/load de projeto).
- Quantitativo: Pé de Galinha (pg1/pg2) contabilizado como SAPATA1/SAPATA2 via novo `addPeDeGalinha()` (quantOverride/excludeFromQuantitative)
- Removido ~55 linhas de código morto em `object:modified` (ramos 2d_fundo/2d_lateral_dir duplicados e inalcançáveis)
- Canvas 2D/3D: zoom scroll + botões (20%-500%)
- Snap magnético entre componentes (toggle, 15px, bordas/centros)
- Removida duplicação automática ao adicionar componentes
- Medidas 3D: linhas de cota (X/Y/Z) e etiquetas individuais por peça
- Sapata Pé de Galinha (1m e 2m): renderização 2D/3D proporcional ao comprimento
- Profundidade liberada para 0 (estruturas 2D)
- Fix: openModal/closeModal/showToast expostos globalmente
- Fix: SyntaxError no branch `sleeve` (Fabric.Rect ausente) no trussCalculator
- Fix: snap magnético em tempo real (evento `moving` em vez de `object:modified`)

## Estado dos Módulos
| Módulo | Status |
|--------|--------|
| Auth | Ativo |
| Dashboard | Ativo |
| Admin/Usuários | Ativo |
| Calculadora Box Truss | Ativo |
| Peças & Estoque | Ativo |
| API | Ativo |
| Clientes | Desativado (código completo, rotas comentadas) |
| Pedidos | Desativado (código completo, rotas comentadas) |

## Descobertas Recentes
- `bootstrap.sh` não existe (referenciado em AGENTS.md e CLAUDE.md mas nunca criado)
- Placeholders ISP em `.ai/` (PPPoE, bloqueio, vencimento, secret) — ignorar, domínio real é Box Truss
- `Home/index.php` tem ~5300 linhas (cresceu de ~2662) contém `trussCalculator()` — monólito Alpine.js + Fabric.js + Three.js
- Router não implementa PUT/DELETE em formulários (só GET/POST)
- PDO é singleton por request
- Módulo API em `routes/pedidos.php` (busca clientes/produtos) fica inativo junto com pedidos
- `RBAC` cacheia permissões em sessão; `invalidate()` deve ser chamado após mudanças
- **Produção real é um servidor remoto diferente do ambiente local** (VPS
  CyberPanel/OpenLiteSpeed, `sisloc.online/profoxtruss`) — descoberto em
  2026-08-18 quando o usuário pediu deploy; até então toda alteração só
  existia localmente. Ver `.ai/context/deployment.md` (2026-08-18)
- API usa `{ok, data}` na resposta JSON, não `{success, data}` como
  `api_patterns.md`/`rules/api_rules.md` documentam — doc desatualizada,
  código está correto e consistente consigo mesmo (ver `obs-013`)
- `php -l` segfaulta no servidor de produção (ionCube Loader?) — validar
  sintaxe sempre localmente antes de enviar, nunca no remoto (2026-08-18)

## Pendências Críticas
Nenhuma.

## Riscos Conhecidos
- Peças `plana`/`braco` têm o comprimento zerado se editadas via Peças &
  Estoque (admin) — bug pré-existente, não corrigido (ver `obs-014`)
- `syncCanvasFrom3D()` derruba a seleção ativa do Fabric.js em qualquer fluxo
  que a chame com algo selecionado — só corrigido no painel de Propriedades
  (`_reselectComp`); outros fluxos (drag comum, rotação, delete, undo/redo)
  provavelmente também desselecionam visualmente, mas isso raramente quebra
  algo funcional (ver `obs-015`)
