<?php
Layout::set('main');
Layout::block('title', '404 — Página não encontrada');
Layout::start('content');
?>
<div style="text-align:center;padding:4rem 0">
    <h1 style="font-size:5rem;font-weight:800;color:#e5e7eb;margin:0">404</h1>
    <p style="font-size:1.25rem;color:#6b7280;margin:.5rem 0 2rem">Essa página não existe.</p>
    <a href="<?= url('/') ?>" style="background:#2563eb;color:#fff;padding:.6rem 1.5rem;border-radius:6px;text-decoration:none">
        Voltar ao início
    </a>
</div>
<?php Layout::end(); ?>


