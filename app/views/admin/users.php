<?php
Layout::set('main');
Layout::block('title', $title);
Layout::start('content');
?>

<h1><?= htmlspecialchars($title) ?></h1>

<?php if (!empty($errors)): ?>
    <?php component('alert', ['type' => 'error', 'message' => implode(' ', $errors)]) ?>
<?php endif; ?>

<?php component('table', ['columns' => $columns, 'rows' => $users]) ?>

<?php Layout::end(); ?>


