# Glossário do Domínio — Box Truss

## Estruturas
- **Box Truss Q30**: Perfil de treliça metálica quadrada (30cm x 30cm) usada em estruturas para eventos, palcos, stands.
- **Pórtico**: Estrutura composta por peças Q30 montadas em formato de pórtico (colunas + viga superior).
- **Sapata**: Base de apoio retangular sólida (4 faces estruturais) para torres Box Truss. Sempre apoiada no solo (Y=0). Largura fixa 30cm, altura fixa 3.5cm, comprimento variável (50/60/80cm). Material: aço escovado. Modelos: SAPATA-30X50, SAPATA-30X60, SAPATA-30X80. Tipo `sapata`.
- **Sleeve**: Conector que une duas peças Q30 (emenda linear).
- **Treliça Plana (PLANA / Flat Truss)**: Treliça de apenas 2 faces paralelas (banzo superior e inferior) ligadas por travessas e diagonais — NÃO forma volume fechado como o Box Truss. Seção: 0,30m de altura x ~0,05m de espessura (apenas os tubos). Pode ser entendida como um Box Truss "aberto" sem as faces laterais. Tem altura, mas praticamente não tem largura estrutural. Conectores nas 2 extremidades, união por pinos/parafusos. Catálogo: PLANA-200 (2m), PLANA-250 (2,5m), PLANA-300 (3m), tipo `plana`.
- **Vão**: Distância entre dois pontos de apoio consecutivos.
- **Braço**: 1 barra estrutural apenas (1 tubo longitudinal) — não possui faces, não forma treliça. Elemento linear com apenas comprimento e direção; conecta-se a cubos, bases ou outras estruturas pela face externa. Terceiro tipo na hierarquia Box Truss (4 faces) > Treliça Plana (2) > Braço (1 barra). Catálogo: BRACO-300 (3m), tipo `braco`.
- **Perfil PM5**: Família de peças DISTINTA do Box Truss Q30 — perfil de alumínio extrudado 50x50mm, seção cruciforme real (cavidade central maior + 4 câmaras de canto, ver `pm5.jpeg`/`descricao_completa_perfil_aluminio_3d.pdf` na raiz do projeto). Dois papéis com orientação travada: **Montante** (tipo `montante`) sempre vertical, nunca deita; **Travessa** (tipo `travessa`) sempre no plano horizontal, nunca empina. Sem conector próprio — as peças se encontram diretamente. Cor é atributo do item de catálogo (varia por comprimento, coluna `produtos.cor`), usada como cor real do render 2D/3D (2026-08-17/18).

## Peças (Produtos)
- **Q30-{comprimento}**: Peça Box Truss com comprimento em centímetros (ex: Q30-300 = 3.00m).
- **Catálogo**: Conjunto de peças disponíveis para montagem, com código, tipo, comprimento, peso, preço e estoque.

## Projetos
- **Projeto**: Estrutura treliçada definida por width, height, length; componentes salvos como JSON.
- **Escala**: Relação pixels/metro no canvas (ex: 50 = 1m = 50px).
- **Snap Magnético**: Alinhamento automático entre componentes no canvas 2D (distância de 15px). Desde 2026-08-18, mostra uma linha-guia tracejada visual no ponto de encaixe (antes o snap acontecia "invisível").
- **Colisão Real**: Sistema de bloqueio de sobreposição entre QUALQUER par de peças (não só a cascata de posicionamento inicial), baseado em caixa orientada → AABB no mundo (função `collidesWithOthers`/`_worldAABB`). Aplicado ao adicionar, arrastar e girar peças nas 5 views 2D. Ver regra completa em `business_rules.md` (2026-08-18).
- **Raio-X**: Modo de visualização (botão 👻, estado `xrayMode`) que deixa toda peça estrutural semi-transparente (35% opacidade) pra enxergar o que está coberto por outra na mesma posição de tela — disponível nos 5 modos 2D e na Vista 3D (2026-08-18).
- **Vista Isométrica**: 4 ângulos de câmera pré-definidos (botões Iso 1-4) que pulam a câmera 3D pra um canto diagonal da estrutura — só existe na Vista 3D (câmera livre via OrbitControls); não tem equivalente nos modos 2D, que são projeções ortogonais fixas por definição (2026-08-18).
- **Painel de Propriedades**: Card na coluna central, abaixo do canvas, visível quando há uma peça selecionada — mostra nome/código/comprimento/peso/cor e os campos de posição X/Y/Z com ajuste fino (▲/▼) e botão Aplicar (2026-08-18).

## Sistema
- **RBAC**: Role-Based Access Control — controle de acesso por cargo (admin, editor, user).
- **Role**: Cargo que define permissões (admin, editor, user).
- **Permissão**: Ação granular do sistema (ex: `user.edit`, `report.export`, `cliente.view`).
- **BASE_PATH**: Path dinâmico calculado a partir de `$_SERVER['SCRIPT_NAME']` para suporte a subdiretórios.

## Observações
- Terminologia de ISP (PPPoE, bloqueio, plano, vencimento, secret) é placeholder herdado e **não** pertence a este sistema.
- O sistema não possui conceitos de faturamento, cobrança ou bloqueio por inadimplência.
