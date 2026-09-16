/**
 * app/views/pedidos/js/pedidos.js
 *
 * Responsabilidades deste arquivo:
 *   1. Estado do formulário (store) — itens, desconto, totais
 *   2. Autocomplete de cliente (busca por nome ou CPF)
 *   3. Autocomplete de produto por item (busca por nome ou código)
 *   4. Cálculo em tempo real ao mudar quantidade ou desconto
 *   5. Renderização dos itens sem refresh
 *   6. Serialização do estado para o campo hidden antes do submit
 *   7. Toast de feedback via ?ok= na URL
 *
 * Depende de (carregados antes deste arquivo no layout):
 *   /assets/js/utils/api.js
 *   /assets/js/utils/autocomplete.js
 *   /assets/js/utils/state.js
 */

document.addEventListener('DOMContentLoaded', () => {

    // ── Só roda na página do formulário ────────────────────────────────────
    const form = document.getElementById('pedido-form');
    if (!form) {
        exibirToast();
        return;
    }

    // ── Estado inicial ───────────────────────────────────────────────────────
    // Pode vir preenchido pelo PHP em modo edição (JSON embutido na view)
    const inicial = window.PEDIDO_INICIAL ?? { desconto: 0, itens: [] };

    const store = createStore(inicial, renderTudo);

    // ── Referências DOM ──────────────────────────────────────────────────────
    const clienteIdInput  = document.getElementById('cliente_id');
    const clienteBuscaEl  = document.getElementById('cliente-busca');
    const descontoInput   = document.getElementById('desconto');
    const itensContainer  = document.getElementById('itens-lista');
    const itensJsonInput  = document.getElementById('itens_json');
    const btnAddItem      = document.getElementById('btn-adicionar-item');

    // ── Autocomplete de cliente ──────────────────────────────────────────────
    if (clienteBuscaEl) {
        autocomplete({
            input:    clienteBuscaEl,
            url:      '/api/clientes/busca',
            minChars: 2,
            onSelect: (item) => {
                clienteIdInput.value  = item.id;
                clienteBuscaEl.value  = item.nome + ' — ' + item.cpf;
            },
            onClear: () => {
                clienteIdInput.value = '';
            },
        });
    }

    // ── Desconto ─────────────────────────────────────────────────────────────
    descontoInput?.addEventListener('input', () => {
        const v = parseFloat(descontoInput.value.replace(',', '.')) || 0;
        store.setState({ desconto: Math.min(100, Math.max(0, v)) });
    });

    // ── Adicionar item vazio ──────────────────────────────────────────────────
    btnAddItem?.addEventListener('click', () => {
        store.addItem('itens', {
            produto_id:   null,
            produto_nome: '',
            preco_unit:   0,
            quantidade:   1,
            subtotal:     0,
        });
    });

    // ── Render principal ──────────────────────────────────────────────────────
    function renderTudo(state) {
        renderItens(state.itens);
        renderTotais(state);
        serializarParaForm(state);
    }

    // ── Render: lista de itens ────────────────────────────────────────────────
    function renderItens(itens) {
        itensContainer.innerHTML = '';

        itens.forEach((item, index) => {
            const row = document.createElement('div');
            row.className = 'item-row';
            row.dataset.index = index;

            row.innerHTML = `
                <div class="form-group" style="margin:0;position:relative">
                    <input
                        type="text"
                        class="produto-busca"
                        value="${esc(item.produto_nome)}"
                        placeholder="Buscar produto por nome ou código…"
                        data-index="${index}"
                    >
                </div>
                <div class="form-group" style="margin:0">
                    <input
                        type="number"
                        class="item-qtd"
                        value="${item.quantidade}"
                        min="1"
                        data-index="${index}"
                    >
                </div>
                <div class="form-group" style="margin:0">
                    <input
                        type="text"
                        class="item-preco"
                        value="${formatarDecimal(item.preco_unit)}"
                        data-index="${index}"
                        readonly
                    >
                </div>
                <div class="item-subtotal">
                    R$ <span class="subtotal-valor">${formatarMoeda(item.subtotal)}</span>
                </div>
                <button type="button" class="btn-remover-item" data-index="${index}" title="Remover item">✕</button>
            `;

            itensContainer.appendChild(row);

            // Autocomplete de produto neste item
            const prodInput = row.querySelector('.produto-busca');
            autocomplete({
                input:    prodInput,
                url:      '/api/produtos/busca',
                minChars: 2,
                onSelect: (prod) => {
                    store.updateItem('itens', index, {
                        produto_id:   prod.id,
                        produto_nome: prod.nome,
                        preco_unit:   parseFloat(prod.preco),
                        subtotal:     calcSubtotal(parseFloat(prod.preco), store.getState().itens[index].quantidade),
                    });
                },
                onClear: () => {
                    store.updateItem('itens', index, {
                        produto_id: null, produto_nome: '', preco_unit: 0, subtotal: 0,
                    });
                },
            });
        });

        // ── Delegação de eventos nos itens ────────────────────────────────────

        // Remover item
        itensContainer.querySelectorAll('.btn-remover-item').forEach(btn => {
            btn.addEventListener('click', () => {
                store.removeItem('itens', parseInt(btn.dataset.index));
            });
        });

        // Mudar quantidade → recalcula subtotal em tempo real
        itensContainer.querySelectorAll('.item-qtd').forEach(input => {
            input.addEventListener('input', () => {
                const i   = parseInt(input.dataset.index);
                const qtd = Math.max(1, parseInt(input.value) || 1);
                const { itens } = store.getState();
                store.updateItem('itens', i, {
                    quantidade: qtd,
                    subtotal:   calcSubtotal(itens[i].preco_unit, qtd),
                });
            });
        });
    }

    // ── Render: totais ────────────────────────────────────────────────────────
    function renderTotais(state) {
        const subtotal = state.itens.reduce((acc, it) => acc + (it.subtotal || 0), 0);
        const desc     = Math.min(100, Math.max(0, state.desconto || 0));
        const valorDesc = subtotal * (desc / 100);
        const total    = subtotal - valorDesc;

        setText('total-subtotal',  formatarMoeda(subtotal));
        setText('total-desconto',  `- R$ ${formatarMoeda(valorDesc)}`);
        setText('total-final',     formatarMoeda(total));
    }

    // ── Serializa estado para o campo hidden antes do submit ──────────────────
    function serializarParaForm(state) {
        itensJsonInput.value = JSON.stringify(
            state.itens
                .filter(it => it.produto_id)   // ignora itens sem produto selecionado
                .map(it => ({
                    produto_id:  it.produto_id,
                    quantidade:  it.quantidade,
                    preco_unit:  it.preco_unit,
                    subtotal:    it.subtotal,
                }))
        );
    }

    // ── Inicializa o store (dispara renderTudo com estado inicial) ────────────
    store.setState({});

    // ── Toast ─────────────────────────────────────────────────────────────────
    exibirToast();
});

// ── Helpers puros ─────────────────────────────────────────────────────────────

function calcSubtotal(preco, qtd) {
    return Math.round(parseFloat(preco) * parseInt(qtd) * 100) / 100;
}

function formatarMoeda(v) {
    return parseFloat(v || 0).toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

function formatarDecimal(v) {
    return parseFloat(v || 0).toFixed(2).replace('.', ',');
}

function esc(str) {
    return String(str ?? '').replace(/"/g, '&quot;').replace(/</g, '&lt;');
}

function setText(id, val) {
    const el = document.getElementById(id);
    if (el) el.textContent = val;
}

function exibirToast() {
    const msgs = { criado: '✓ Pedido criado.', atualizado: '✓ Pedido atualizado.', deletado: '✓ Pedido excluído.' };
    const msg  = msgs[new URLSearchParams(location.search).get('ok')];
    if (!msg) return;
    const t = Object.assign(document.createElement('div'), { className: 'toast', textContent: msg });
    document.body.appendChild(t);
    setTimeout(() => t.remove(), 3500);
    history.replaceState({}, '', location.pathname);
}


