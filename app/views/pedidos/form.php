<?php
$editando = $pedido !== null;

Layout::set('main');
Layout::block('title', $editando ? 'Editar Pedido' : 'Novo Pedido');

// CSS do módulo
Layout::block('head', '<link rel="stylesheet" href="' . url('assets/pedidos/pedidos.css') . '">');

// JS: utils compartilhados ANTES do JS do módulo
Layout::block('scripts',
    '<script src="' . url('assets/js/utils/api.js') . '"></script>' .
    '<script src="' . url('assets/js/utils/autocomplete.js') . '"></script>' .
    '<script src="' . url('assets/js/utils/state.js') . '"></script>' .
    '<script>
        // Estado inicial injetado pelo PHP — em edição, vem com os itens do banco
        window.PEDIDO_INICIAL = ' . json_encode([
            'desconto' => (float) ($pedido['desconto'] ?? 0),
            'itens'    => array_map(fn($it) => [
                'produto_id'   => $it['produto_id'],
                'produto_nome' => $it['produto_nome'] ?? '',
                'preco_unit'   => (float) $it['preco_unit'],
                'quantidade'   => (int)   $it['quantidade'],
                'subtotal'     => (float) $it['subtotal'],
            ], $itens),
        ]) . ';
        window.BASE_URL = "' . BASE_URL . '";
    </script>' .
    '<script src="' . url('assets/pedidos/pedidos.js') . '"></script>'
);

Layout::start('content');
?>

<div class="pedidos-toolbar">
    <h1><?= $editando ? "Editar Pedido #{$pedido['id']}" : 'Novo Pedido' ?></h1>
    <a href="<?= url('/pedidos') ?>" class="btn btn-secondary">← Voltar</a>
</div>

<div class="pedido-form-card">
<form
    id="pedido-form"
    method="POST"
    action="<?= $editando ? url("/pedidos/{$pedido['id']}") : url('/pedidos') ?>"
    novalidate
>
    <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
    <!-- Itens serializados pelo JS antes do submit -->
    <input type="hidden" name="itens_json" id="itens_json" value="">

    <?php if (!empty($erros)): ?>
        <div style="background:#fef2f2;border:1px solid #fecaca;color:#dc2626;padding:.75rem 1rem;border-radius:6px;margin-bottom:1.5rem;font-size:.9rem">
            <?= implode('<br>', array_map('htmlspecialchars', $erros)) ?>
        </div>
    <?php endif; ?>

    <!-- ── Seção: Cliente ──────────────────────────────────────────────────── -->
    <div class="form-section">
        <div class="form-section-title">Cliente</div>
        <div class="form-row">
            <div class="form-group" style="position:relative">
                <label for="cliente-busca">Buscar cliente (nome ou CPF)</label>
                <input
                    type="text"
                    id="cliente-busca"
                    placeholder="Digite para buscar…"
                    value="<?= htmlspecialchars($pedido['cliente_nome'] ?? '') ?>"
                    autocomplete="off"
                >
                <!-- Campo hidden que vai no POST -->
                <input type="hidden" name="cliente_id" id="cliente_id"
                       value="<?= (int) ($pedido['cliente_id'] ?? 0) ?>">
                <?php if (!empty($erros['cliente_id'])): ?>
                    <span class="form-erro"><?= htmlspecialchars($erros['cliente_id']) ?></span>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="desconto">Desconto (%)</label>
                <input
                    type="number"
                    name="desconto"
                    id="desconto"
                    value="<?= (float) ($pedido['desconto'] ?? 0) ?>"
                    min="0"
                    max="100"
                    step="0.01"
                    placeholder="0"
                >
                <?php if (!empty($erros['desconto'])): ?>
                    <span class="form-erro"><?= htmlspecialchars($erros['desconto']) ?></span>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- ── Seção: Itens ────────────────────────────────────────────────────── -->
    <div class="form-section">
        <div class="form-section-title">Itens do pedido</div>

        <?php if (!empty($erros['itens'])): ?>
            <span class="form-erro" style="display:block;margin-bottom:.75rem">
                <?= htmlspecialchars($erros['itens']) ?>
            </span>
        <?php endif; ?>

        <div class="itens-header">
            <span>Produto</span>
            <span>Qtd</span>
            <span>Preço unit.</span>
            <span>Subtotal</span>
            <span></span>
        </div>

        <!-- Itens renderizados e gerenciados pelo JS -->
        <div id="itens-lista"></div>

        <button type="button" id="btn-adicionar-item" class="btn btn-secondary btn-sm btn-adicionar-item">
            + Adicionar item
        </button>
    </div>

    <!-- ── Totais ──────────────────────────────────────────────────────────── -->
    <div class="pedido-totais">
        <table>
            <tr>
                <td>Subtotal</td>
                <td>R$ <span id="total-subtotal">0,00</span></td>
            </tr>
            <tr>
                <td>Desconto</td>
                <td><span id="total-desconto">- R$ 0,00</span></td>
            </tr>
            <tr class="linha-total">
                <td>Total</td>
                <td>R$ <span id="total-final">0,00</span></td>
            </tr>
        </table>
    </div>

    <!-- ── Observação ──────────────────────────────────────────────────────── -->
    <div class="form-section" style="margin-top:1.5rem">
        <div class="form-group">
            <label for="observacao">Observação</label>
            <textarea name="observacao" id="observacao" rows="3"
                      placeholder="Opcional…"><?= htmlspecialchars($pedido['observacao'] ?? '') ?></textarea>
        </div>
    </div>

    <div class="form-acoes">
        <button type="submit" class="btn btn-primary">
            <?= $editando ? 'Salvar alterações' : 'Criar pedido' ?>
        </button>
        <a href="<?= url('/pedidos') ?>" class="btn btn-secondary">Cancelar</a>
    </div>

</form>
</div>

<?php Layout::end(); ?>


