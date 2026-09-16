# Regras de Negócio

## Autenticação e Acesso
- Login com email + password (hash bcrypt via `password_verify`)
- Sessão com `SESSION_LIFETIME` configurável (default 7200s)
- Três roles: `admin` (acesso total), `editor` (dashboard + relatórios), `user` (dashboard apenas)
- RBAC granular: cada ação do sistema tem permissão correspondente
- Permissões são cacheadas em sessão; `RbacService::invalidate()` deve ser chamado após alterações

## Projetos (Calculadora Box Truss)
- Projetos são definidos por dimensões (width, height, length — em metros)
- Depth (profundidade) pode ser 0 para estruturas 2D
- Componentes do projeto são armazenados como JSON na coluna `components` da tabela `projects`
- Peças do catálogo podem ser adicionadas ao canvas por drag-and-drop ou clique
- Snap magnético alinha componentes automaticamente (15px de distância, bordas e centros)
- Escala ajustável (1m = 20px ou 50px), zoom de 20% a 500%
- Visualização 3D com linhas de cota (X/Y/Z) e etiquetas de medida por peça

## Regras de Montagem (geometria física — ditadas pelo usuário, 2026-06-11)
- O cubo conector NÃO é um ponto matemático: é um sólido de 0,30m x 0,30m x 0,30m.
- Treliças nunca ocupam o mesmo volume do cubo; a conexão ocorre na FACE EXTERNA do cubo.
- Dimensão final = dimensão da peça + dimensão do cubo quando alinhados na mesma direção.
  Ex.: Q30 de 1m conectada ao TOPO do cubo => altura total 1,30m (1,00m + 0,30m).
  Ex.: Q30 de 1m conectada à LATERAL do cubo => acrescenta 1,00m além dos 0,30m do cubo.
- PROIBIDO gerar interseção entre sólidos; toda peça é posicionada usando seu volume real.
- Taxonomia de tipos estruturais (propriedade conceitual `faces` — ditada pelo
  usuário em 2026-06-11; usar ao gerar pórticos/grids/travamentos/balanços):
  ```json
  { "box_truss": { "faces": 4 }, "trelica_plana": { "faces": 2 }, "braco": { "faces": 1 } }
  ```
  - BOX_TRUSS (tipo `Q30`): 4 faces estruturais / 4 tubos longitudinais, volume
    fechado, seção quadrada 0,30m x 0,30m (existem Q25/Q40 no mercado),
    conectores nas 2 extremidades, união por pinos/parafusos.
  - TRELICA_PLANA (tipo `plana`): 2 faces / 2 tubos longitudinais ligados por
    diagonais, estrutura aberta e plana, SEM volume fechado, seção 0,30m
    (altura) x ~0,05m (espessura). Usada para travamentos e estruturas leves.
    Convenção de render: plano da treliça sempre vertical ("em pé").
  - BRACO (tipo `braco`): 1 barra estrutural apenas — NÃO possui faces, NÃO
    forma treliça. Elemento LINEAR: possui apenas comprimento e direção.
    Pode ser conectado a cubos, bases ou outras estruturas. Ex.: braço de 1m
    conectado a um cubo de 0,30m => comprimento físico total 1,30m (a barra
    inicia na FACE EXTERNA do cubo, não no centro).
  - Complexidade estrutural: Box Truss > Treliça Plana > Braço.

## SAPATA — Regras Específicas (2026-06-15)
- **Função**: Base de apoio para torres e estruturas verticais Box Truss. NÃO é
  utilizada para vencer vãos horizontais.
- **Geometria**: Objeto SÓLIDO retangular (4 faces estruturais). Dimensões:
  largura fixa 0.30m, altura fixa 0.035m (3.5cm), comprimento variável por
  modelo (0.50m / 0.60m / 0.80m). Origem centralizada no volume.
- **Solo (Y=0)**: A base inferior DEVE permanecer em contato com o solo.
  `compY = altura/2 = 0.0175`. PROIBIDO posicionar SAPATA suspensa.
  Constraint aplicada em addComponent, object:modified, nudgeObject.
- **Conexão**: A face superior (Y = +0.0175) é o ponto de conexão da torre
  Box Truss. Peças acima da sapata devem ser posicionadas considerando o
  volume sólido (face externa, sem interseção).
- **Interseção**: NENHUMA outra peça pode ocupar o mesmo volume da SAPATA.
  Garantido pelo sistema de Colisão Real (ver seção própria abaixo) — SAPATA
  usa `_pieceHalfExtents` = `[0.15, 0.0175, length/2]`.
- **Rotação**: Permitida em todos os eixos (rotationX/Y/Z). Na Vista Superior
  (planta), rotacionar orienta a pegada/footprint no plano horizontal.
- **Quantitativo**: SAPATA conta como peça individual no quantitativo E como
  metro linear (`comp.length`, ex: 0.50m/0.60m/0.80m por unidade).
- **Catálogo**: 3 modelos (SAPATA-30X50/30X60/30X80), tipo `sapata`,
  campo `comprimento` editável no formulário de Peças & Estoque.

## PERFIL PM5 — Montante/Travessa (2026-08-17)
- **Família distinta do Box Truss Q30**: perfil de alumínio extrudado 50x50mm,
  seção cruciforme (cavidade central maior + 4 câmaras menores nos cantos — ver
  `pm5.jpeg` e `descricao_completa_perfil_aluminio_3d.pdf` na raiz do projeto).
  3D fiel à seção real desde 2026-08-18 (`_pm5CrossSection`/`_createPM5Mesh`,
  `THREE.ExtrudeGeometry` com 5 furos sobre o contorno 50x50mm) — decisão
  INICIAL (2026-08-17) tinha sido usar um prisma simplificado, revertida
  quando o usuário comparou o resultado com a foto de referência e apontou que
  não batia. Dimensões dos furos (parede 2,5mm, câmara central 16mm, câmaras
  de canto 12mm) são estimativa visual da foto, não medida técnica do
  fabricante — se o usuário fornecer o desenho/CAD real, ajustar os números em
  `_pm5CrossSection` sem mudar a arquitetura.
- **Dois papéis estruturais, cada um com restrição de orientação rígida**
  (ditada pelo usuário, enforced em `addComponent`/`object:modified`):
  - `montante`: SEMPRE vertical (ao longo de Y). Nunca pode deitar.
    Rotação totalmente travada (rotationX=rotationY=rotationZ=0); nasce
    vertical na própria geometria 3D, sem precisar de rotação de base.
  - `travessa`: SEMPRE no plano horizontal (X-Z). Nunca pode empinar.
    rotationX e rotationZ travados em 0; só rotationY é livre (direção no
    plano horizontal — permite diagonal, igual ao Sketch do Q30).
- **Cor é atributo do item de catálogo** (coluna `produtos.cor`, hex), não do
  tipo — cada comprimento tem sua própria cor fixa, usada como cor REAL do
  render 2D (fill/stroke) e 3D (material), decisão explícita do usuário
  (diferente da Lona, cuja cor é escolhida ad-hoc pelo usuário no momento de
  adicionar). Ver `getComponentColors()` em `home/index.php`.
- **Sem conector próprio**: montante e travessa se encontram diretamente
  (topo a topo/parafusado), sem peça intermediária tipo cubo/grepo — decisão
  explícita do usuário. Se um conector for especificado no futuro, revisar
  esta regra.
- **Catálogo seed** (peso/preço não informados pelo usuário, ficaram 0.00 —
  editáveis em Peças & Estoque):
  | Código | Tipo | Comprimento | Cor | Estoque |
  |---|---|---|---|---|
  | TRAV-045 | travessa | 0.45m | azul `#3b82f6` | 700 |
  | TRAV-095 | travessa | 0.95m | verde `#22c55e` | 700 |
  | TRAV-195 | travessa | 1.95m | vermelho `#ef4444` | 400 |
  | MONT-100 | montante | 1.00m | laranja `#f97316` | 400 |
  | MONT-220 | montante | 2.20m | marrom `#92400e` | 180 |
  | MONT-250 | montante | 2.50m | roxo `#a855f7` | 270 |
  | MONT-300 | montante | 3.00m | cinza `#9ca3af` | 80 |
- **Quantitativo**: entram em metros lineares, mas em métrica SEPARADA
  ("Metros Lineares (Perfil PM5)") da métrica "Metros Lineares (Box Truss)" —
  são famílias de perfil diferentes, misturar os dois totais sob o rótulo
  "(Box Truss)" seria incorreto.

## Dimensões Reais dos Conectores — SLEEVE e GREPO (corrigido 2026-08-18)
Fonte única de verdade pras 3 representações (colisão, 3D, 2D) — reanálise
completa pedida pelo usuário ("não invente medidas, use medidas exatas").
- **SLEEVE**: MESMA geometria do Q30/Cubo (`createTrussSection`, seção
  0,30m x 0,30m fixa), só que 0,42m no eixo do comprimento — NÃO é um cubo
  uniforme. Half-extents corretos: `[0.21, 0.15, 0.15]` (comprimento/2,
  seção/2, seção/2). Ícone 2D: quadrado de 0,42m (era 0,44m aproximado).
- **GREPO**: perfil "U" de aço galvanizado, chassi real **300 x 42 x 30mm**
  (constantes `length=0.30`, `channelWidth=0.042`, `channelHeight=0.030` em
  `createGrepoModel`). Half-extents corretos: `[0.015, 0.15, 0.021]` (altura/2,
  comprimento/2, largura/2) — NÃO é um cubo de 0,30m como o Cubo conector.
  Ícone 2D CONTINUA usando o quadrado de 0,30m do Cubo por decisão consciente
  (chassi real de 30-42mm seria minúsculo demais pra clicar/ver na tela) — só
  a colisão (física real) foi corrigida, o ícone é uma simplificação visual
  deliberada, não um erro.
- Bug relacionado (mesma investigação): o dispatch 3D do Sleeve e do Grepo
  sobrescrevia (`model.rotation.z = ...`) em vez de acumular
  (`model.rotation.z += ...`) a rotação — como os dois modelos já nascem com
  uma rotação de base própria internamente, isso apagava essa base e
  desalinhava o modelo renderizado. Corrigido pra `+=`, igual a todos os
  outros tipos (Q30/plana/braco/cubo/sapata/montante/travessa).

## Colisão Real Entre Peças (2026-08-18)
Sistema geral, vale pra QUALQUER par de peças (Q30, plana, braco, sapata,
cubo, grepo, sleeve, montante, travessa) — não é exclusivo de nenhuma família.
- **Motivação**: usuário reportou que peças "entravam uma dentro da outra"
  (visível principalmente no Perfil PM5, seção fina de 5cm) — a cascata antiga
  de `addComponent` era só heurística de posição/comprimento (registrada como
  bug em `obs-011`), sem verificar volume/rotação real.
- **Mecânica**: cada peça vira uma caixa orientada (half-extents locais fixos
  por tipo, ver `_pieceHalfExtents` em `home/index.php`) rotacionada pela MESMA
  matemática do render 3D (`_pieceRotationRad`), daí computa-se a AABB
  (bounding box alinhada aos eixos) no mundo (`_worldAABB`). Duas peças
  colidem se suas AABBs se sobrepõem (`_aabbOverlap`) — `collidesWithOthers(comp,
  excludeUids)` é o ponto de entrada.
- **Margem de toque**: cada eixo da caixa é encolhido antes do teste, mas o
  encolhimento é capado em 40% do half-extent daquele eixo
  (`Math.min(margin, h * 0.4)`, margin-base = 0.008m). Isso é ESSENCIAL —
  uma margem fixa (era 0.02m antes do fix) "come" proporcionalmente muito mais
  de peças finas (PM5, half-extent 0.025m) do que de peças grossas (Q30,
  half-extent 0.15m), causando falso-negativo (deixa passar sobreposição real)
  justamente nas peças mais finas. Sem NENHUMA margem, peças que devem
  encostar-se (Q30 num cubo, travessa na face de um montante) seriam
  incorretamente bloqueadas — a margem existe pra permitir toque real, não
  pra tolerar overlap.
- **Aproximação diagonal**: peças rotacionadas (Sketch, diagonal) usam a AABB
  da caixa rotacionada, que é maior que a caixa real — bloqueio um pouco
  conservador em diagonal é aceito de propósito (melhor bloquear demais um
  caso raro que deixar passar uma sobreposição real).
- **Onde é aplicado** (bloqueia com reversão pro estado anterior + toast, nunca
  silenciosamente sobrepõe):
  - **Adicionar** (`addComponent`, catálogo/drag-and-drop): cascateia até achar
    espaço livre real (60 tentativas); se não achar, recusa adicionar.
  - **Arrastar** (`object:modified`): snapshot de `components3D` antes do
    gesto; se a posição final colidir com algo FORA do grupo movido, restaura
    o snapshot inteiro.
  - **Girar** (alça de rotação E duplo-clique horizontal/vertical): mesma
    lógica, restaura só a rotação se colidir.
  - **Painel de Propriedades / campos X/Y/Z**: `_setSelectedAxis`/
    `applyAllAxesInput` fazem a mesma checagem antes de commitar.
- **Fora do escopo** (empurram peças pra dentro de outras sem checar, decisão
  consciente de não estender ainda): geração via Sketch/Auto-Layout, copiar/
  colar, duplicar — inserem direto em `components3D` sem passar por
  `addComponent`.

## Peças e Estoque
- Peças têm tipo (Q30, sleeve, sapata, plana, braco, cubo, grepo), comprimento em metros, peso em kg, preço e estoque
- Peças podem ser ativadas/desativadas (coluna `ativo`)
- Estoque é decrementado na criação de pedidos (apenas via API)
- SAPATA: campo `comprimento` é editável (não forçado a 0), default 0.50m se vazio
- Catálogo inclui sapatas com renderização 2D/3D própria (ver regras SAPATA acima)

## Clientes (Módulo Desativado)
- CPF é campo único e obrigatório
- CPF armazenado formatado como `000.000.000-00`
- CPF validado pelo algoritmo de dígitos verificadores brasileiro (no controller)
- Clientes podem ser pesquisados por nome ou CPF (autocomplete)

## Pedidos (Módulo Desativado)
- Status possíveis: `rascunho`, `confirmado`, `cancelado`
- Subtotal de itens é recalculado server-side (preco_unit * quantidade)
- Desconto é percentual, aplicado sobre o total
- Itens de pedido têm preço copiado do produto no momento da venda (preco_unit)
- Pedidos com status `confirmado` não podem ser deletados (foreign key com `ON DELETE RESTRICT` em cliente)

## Usuários
- Email único no sistema
- Role atribuída no cadastro, editável via painel admin
- Admin pode criar, editar, listar e excluir usuários
- Permissões são gerenciadas por role via painel admin (grant/revoke)
