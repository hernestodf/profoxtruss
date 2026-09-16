<?php
Layout::set('main');
Layout::block('title', 'Pedidos');
Layout::block('head',    '<link rel="stylesheet" href="' . url('assets/pedidos/pedidos.css') . '">');
Layout::block('scripts', '<script src="' . url('assets/pedidos/pedidos.js') . '"></script>');
Layout::start('content');
?>

<div class="pedidos-toolbar">
    <h1>Pedidos</h1>
    <?php if (RbacService::can('pedido.create')): ?>
        <a href="<?= url('/pedidos/novo') ?>" class="btn btn-primary">+ Novo pedido</a>
    <?php endif; ?>
</div>

<div class="pedidos-table-wrap">
    <?php if (empty($pedidos)): ?>
        <p style="padding:3rem;text-align:center;color:#9ca3af">Nenhum pedido cadastrado.</p>
    <?php else: ?>
        <table class="pedidos-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Cliente</th>
                    <th>Itens</th>
                    <th>Total</th>
                    <th>Status</th>
                    <th>Data</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($pedidos as $p): ?>
                <tr>
                    <td><?= $p['id'] ?></td>
                    <td><?= htmlspecialchars($p['cliente_nome']) ?></td>
                    <td>—</td>
                    <td>R$ <?= number_format($p['total'], 2, ',', '.') ?></td>
                    <td><span class="badge badge-<?= $p['status'] ?>"><?= ucfirst($p['status']) ?></span></td>
                    <td><?= DateUtil::toBrFull($p['created_at']) ?></td>
                    <td style="display:flex;gap:.5rem">
                        <?php if (RbacService::can('pedido.edit')): ?>
                            <a href="<?= url("/pedidos/{$p['id']}/editar") ?>" class="btn btn-secondary btn-sm">Editar</a>
                        <?php endif; ?>
                        <?php if (RbacService::can('pedido.delete')): ?>
                            <form method="POST" action="<?= url("/pedidos/{$p['id']}/deletar") ?>">
                                <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
                                <button class="btn btn-danger btn-sm"
                                    onclick="return confirm('Excluir pedido #<?= $p['id'] ?>?')">
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


