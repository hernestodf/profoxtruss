<?php
/**
 * Componente de tabela genérica.
 * Uso: component('table', ['columns' => [...], 'rows' => [...]])
 *
 * $columns = ['id' => 'ID', 'name' => 'Nome', 'email' => 'E-mail']
 * $rows    = $repo->all()
 */
?>
<div style="overflow-x:auto">
<table style="width:100%;border-collapse:collapse;font-size:.9rem">
    <thead>
        <tr style="background:#f9fafb;border-bottom:2px solid #e5e7eb">
            <?php foreach ($columns as $label): ?>
                <th style="padding:.6rem 1rem;text-align:left;font-weight:600;color:#374151">
                    <?= htmlspecialchars($label) ?>
                </th>
            <?php endforeach; ?>
        </tr>
    </thead>
    <tbody>
        <?php if (empty($rows)): ?>
            <tr>
                <td colspan="<?= count($columns) ?>" style="padding:2rem;text-align:center;color:#9ca3af">
                    Nenhum registro encontrado.
                </td>
            </tr>
        <?php else: ?>
            <?php foreach ($rows as $row): ?>
                <tr style="border-bottom:1px solid #f3f4f6">
                    <?php foreach (array_keys($columns) as $key): ?>
                        <td style="padding:.6rem 1rem;color:#1f2937">
                            <?= htmlspecialchars((string)($row[$key] ?? '—')) ?>
                        </td>
                    <?php endforeach; ?>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
    </tbody>
</table>
</div>


