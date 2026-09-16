<?php
/**
 * Seed runner — executa migration.sql e seed.sql via navegador ou CLI.
 * Após rodar, delete este arquivo por segurança.
 */

require __DIR__ . '/config.php';

$pdo = getPDO();

$results = [];

foreach (['database/migration.sql', 'database/seed.sql'] as $file) {
    $sql = file_get_contents(__DIR__ . '/' . $file);
    try {
        $pdo->exec($sql);
        $results[$file] = 'OK';
    } catch (PDOException $e) {
        $results[$file] = 'ERRO: ' . $e->getMessage();
    }
}

$pdo = null;

$allOk = !in_array(false, array_map(fn($r) => str_starts_with($r, 'OK'), $results), true);

?>
<!DOCTYPE html>
<html lang="pt">
<head>
<meta charset="utf-8">
<title>Seed — ProFoxTruss</title>
<meta name="viewport" content="width=device-width,initial-scale=1">
<style>
body{font-family:monospace;background:#0f172a;color:#e2e8f0;display:flex;align-items:center;justify-content:center;min-height:100vh;margin:0}
.card{background:#1e293b;border:1px solid #334155;border-radius:12px;padding:2rem;max-width:480px;width:100%}
h1{color:<?= $allOk ? '#22c55e' : '#ef4444' ?>;font-size:1.25rem;margin:0 0 1.5rem}
.row{display:flex;justify-content:space-between;padding:.5rem 0;border-bottom:1px solid #334155}
.file{color:#94a3b8}
.status{font-weight:700}
.ok{color:#22c55e}
.err{color:#ef4444}
.del{color:#f59e0b;margin-top:1.5rem;font-size:.8rem}
</style>
</head>
<body>
<div class="card">
<h1><?= $allOk ? '✓ Banco populado com sucesso' : '✗ Erro ao popular banco' ?></h1>
<?php foreach ($results as $file => $status): ?>
<div class="row">
<span class="file"><?= htmlspecialchars($file) ?></span>
<span class="status <?= str_starts_with($status, 'OK') ? 'ok' : 'err' ?>"><?= htmlspecialchars($status) ?></span>
</div>
<?php endforeach; ?>
<p class="del">Após confirmar que o sistema está funcionando, delete este arquivo (seed.php) por segurança.</p>
</div>
</body>
</html>
