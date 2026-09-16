<?php
Layout::set('main');
Layout::block('title', 'Clientes');

// CSS e JS do módulo — carregados APENAS neste módulo
Layout::block('head', '<link rel="stylesheet" href="' . url('assets/clientes/clientes.css') . '">');
Layout::block('scripts', '<script src="' . url('assets/clientes/clientes.js') . '"></script>');

Layout::start('content');
?>

<div class="clientes-toolbar">
    <h1>Clientes</h1>
    <?php if (RbacService::can('cliente.create')): ?>
        <a href="<?= url('/clientes/novo') ?>" class="btn btn-primary">+ Novo cliente</a>
    <?php endif; ?>
</div>

<div class="clientes-table-wrap">
    <?php if (empty($clientes)): ?>
        <p class="clientes-vazio">Nenhum cliente cadastrado ainda.</p>
    <?php else: ?>
        <table class="clientes-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Nome</th>
                    <th>CPF</th>
                    <th>Cadastro</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($clientes as $c): ?>
                <tr>
                    <td><?= $c['id'] ?></td>
                    <td><?= htmlspecialchars($c['nome']) ?></td>
                    <td><?= htmlspecialchars($c['cpf']) ?></td>
                    <td><?= DateUtil::toBrFull($c['created_at']) ?></td>
                    <td class="td-acoes">
                        <?php if (RbacService::can('cliente.edit')): ?>
                            <a href="<?= url("/clientes/{$c['id']}/editar") ?>" class="btn btn-secondary btn-sm">Editar</a>
                        <?php endif; ?>

                        <?php if (RbacService::can('cliente.delete')): ?>
                            <form method="POST" action="<?= url("/clientes/{$c['id']}/deletar") ?>" style="display:inline">
                                <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
                                <button type="submit" class="btn btn-danger btn-sm"
                                    onclick="return confirmarDelete(this.form, '<?= htmlspecialchars(addslashes($c['nome'])) ?>')">
                                    Excluir
                                </button>
                            </form>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<?php Layout::end(); ?>


