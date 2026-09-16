<?php
$editando = $cliente !== null;

Layout::set('main');
Layout::block('title', $editando ? 'Editar Cliente' : 'Novo Cliente');

// CSS e JS do módulo
Layout::block('head', '<link rel="stylesheet" href="' . url('assets/clientes/clientes.css') . '">');
Layout::block('scripts', '<script src="' . url('assets/clientes/clientes.js') . '"></script>');

Layout::start('content');
?>

<div class="clientes-toolbar">
    <h1><?= $editando ? 'Editar Cliente' : 'Novo Cliente' ?></h1>
    <a href="<?= url('/clientes') ?>" class="btn btn-secondary">← Voltar</a>
</div>

<div class="clientes-form-card">
    <form
        method="POST"
        action="<?= $editando ? url("/clientes/{$cliente['id']}") : url('/clientes') ?>"
        class="clientes-form"
        novalidate
    >
        <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">

        <!-- Nome -->
        <div class="form-group">
            <label for="nome">Nome completo</label>
            <input
                type="text"
                id="nome"
                name="nome"
                value="<?= htmlspecialchars($cliente['nome'] ?? old('nome', '')) ?>"
                placeholder="Ex: João da Silva"
                maxlength="100"
                autocomplete="off"
                class="<?= isset($erros['nome']) ? 'campo-erro' : '' ?>"
            >
            <?php if (!empty($erros['nome'])): ?>
                <span class="form-erro"><?= htmlspecialchars($erros['nome']) ?></span>
            <?php endif; ?>
        </div>

        <!-- CPF com máscara automática -->
        <div class="form-group">
            <label for="cpf">CPF</label>
            <input
                type="text"
                id="cpf"
                name="cpf"
                value="<?= htmlspecialchars($cliente['cpf'] ?? old('cpf', '')) ?>"
                placeholder="000.000.000-00"
                maxlength="14"
                inputmode="numeric"
                autocomplete="off"
                data-mask="cpf"
                class="<?= isset($erros['cpf']) ? 'campo-erro' : '' ?>"
            >
            <span class="form-erro" id="erro-cpf">
                <?= htmlspecialchars($erros['cpf'] ?? '') ?>
            </span>
        </div>

        <div class="form-acoes">
            <button type="submit" class="btn btn-primary">
                <?= $editando ? 'Salvar alterações' : 'Cadastrar cliente' ?>
            </button>
            <a href="<?= url('/clientes') ?>" class="btn btn-secondary">Cancelar</a>
        </div>
    </form>
</div>

<?php Layout::end(); ?>


