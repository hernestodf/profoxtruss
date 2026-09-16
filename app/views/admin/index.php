<?php
// Define qual layout usar
Layout::set('main');

// Bloco simples
Layout::block('title', 'Painel Admin');

// Bloco com HTML
Layout::start('content');
?>

<h1>Painel Admin</h1>
<p>Bem-vindo, <strong><?= htmlspecialchars($userName) ?></strong>.</p>

<ul style="margin-top:1rem;line-height:2">
    <li><a href="<?= BASE_URL ?>/admin/users">Gerenciar Usuários</a></li>
    <li><a href="<?= BASE_URL ?>/admin/reports">Relatórios</a></li>
</ul>

<?php Layout::end(); ?>


