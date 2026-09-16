<?php
/**
 * Componente de alerta.
 * Uso: component('alert', ['type' => 'error', 'message' => 'Ops!'])
 * Types: error | success | warning | info
 */
$colors = [
    'error'   => ['#fef2f2', '#dc2626', '#fee2e2'],
    'success' => ['#f0fdf4', '#16a34a', '#dcfce7'],
    'warning' => ['#fffbeb', '#d97706', '#fef3c7'],
    'info'    => ['#eff6ff', '#2563eb', '#dbeafe'],
];
[$bg, $text, $border] = $colors[$type ?? 'info'];
?>
<div style="background:<?= $bg ?>;color:<?= $text ?>;border:1px solid <?= $border ?>;padding:.75rem 1rem;border-radius:6px;margin-bottom:1rem;font-size:.9rem">
    <?= htmlspecialchars($message ?? '') ?>
</div>


