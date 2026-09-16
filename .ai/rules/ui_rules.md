# Regras: UI

## Layout
- Layout único: `app/views/layouts/main.php` com sidebar + navbar + drawer.
- Extensão via `Layout::set('main')` no início da view.
- Blocos disponíveis: `title`, `head`, `content`, `scripts`.
- Sidebar com navegação: Calculadora & Grid, Peças & Estoque, Admin (role-based).
- User drawer (direita) para perfil/logout.

## Design System
- **Tema**: Neon escuro com CSS custom properties (`--color-neon-cyan`, `--color-neon-purple`, etc.).
- **Tailwind**: Usado para layout (grid, flex, spacing, responsividade).
- **Componentes**: Web Components customizados com prefixo `Ui` (19+ componentes).
  - `UiModal`, `UiPopover`, `UiDrawer`, `UiToastContainer`, `UiAlert`
  - `UiFormInput`, `UiCheckbox`, `UiRadio`, `UiToggle`, `UiFileUpload`
  - `UiBadge`, `UiCard`, `UiStatCard`, `UiProgress`, `UiCalendar`
  - `UiAccordion`, `UiCarousel`, `UiListItem`, `UiInvoice`
- CSS atômico em partials (`_buttons.css`, `_forms.css`, `_badges.css`, etc.).
- Classes padrão: `.btn`, `.btn-{color}`, `.badge`, `.tog`, `.fi`, `.fl`, `.fg`.

## Responsividade
- Grid de 12 colunas com `xl:grid-cols-12`.
- Sidebar colapsável (mini-mode via Alpine `x-data="layout"`).
- Mobile: sidebar vira overlay com backdrop.

## Canvas (Calculadora)
- Canvas 2D com Fabric.js, toolbar com zoom, escala, snap toggle.
- Canvas 3D com Three.js para visualização.
- Componentes arrastáveis do catálogo para o canvas (drag-and-drop + clique).
- Zoom: 20%-500% com botões + scroll do mouse.
- Layout de 3 colunas: catálogo (esquerda) → canvas + toolbar + **painel de
  Propriedades** (centro) → Quantitativo & Estoque (direita) (2026-08-18).
- **Painel de Propriedades** (`app/views/home/index.php`, coluna central, logo
  abaixo do "Canvas Board Card"): visível só com `x-show="selectedComp"` (peça
  única selecionada, não seleção múltipla). Mostra nome/código/comprimento/peso/
  cor (grid 2/4 colunas) + posição X/Y/Z (campo numérico + botões ▲/▼ de 5cm +
  botão "Aplicar" único que aplica os 3 eixos como uma posição final). Campo Y
  some pra SAPATA (sempre no solo, não editável). Convenção pro estado Alpine:
  `selectedCompUid`/`selectedComp` (getter), `xInput`/`heightInput`/`zInput`,
  métodos genéricos `applyAxisInput(axis, inputProp)`/`nudgeSelectedAxis(axis,
  inputProp, delta)`/`applyAllAxesInput()` — não duplicar lógica por eixo.
- **Raio-X** (botão 👻, estado `xrayMode`): presente na toolbar dos 5 modos 2D E
  na Vista 3D — reduz a opacidade de toda peça estrutural pra ver o que está
  coberto por outra. Acessórios/labels ficam de fora (não são Mesh no 3D, não
  passam pelo bloco de finalização de objeto no 2D).
- **Vistas Isométricas** (botões Iso 1-4): só existem na Vista 3D (câmera livre
  via OrbitControls) — nunca adicionar botão "isométrico" nos modos 2D, que são
  projeções ortogonais fixas por definição, sem câmera pra mover.
- Feedback de arrasto (2026-08-18): label de posição ao vivo seguindo o cursor
  + linha-guia tracejada no ponto de snap, ambos limpos no `object:modified`
  (fim do gesto). Ver `magneticSnap()`/`_updateDragLabel`/`_showSnapGuide`.

## Novas Views
- Seguir o padrão: `Layout::set('main')` + blocos `start('content')`/`end()`.
- Usar componentes UI em vez de HTML puro sempre que possível.
- CSRF: incluir `<?= csrfToken() ?>` em formulários POST.
- Scripts específicos da página no bloco `scripts`.
