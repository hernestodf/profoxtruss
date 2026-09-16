<?php
/**
 * setup.php — Diagnóstico + Seed para ProFoxTruss no cPanel
 * Acesse: https://seusite.com/profoxtruss/setup.php
 * Delete este arquivo após o uso.
 */
header('Content-Type: text/html; charset=utf-8');
mb_internal_encoding('UTF-8');

$root = __DIR__;
$checks = [];
$errors = [];

// ─── PHP version ───
$phpv = PHP_VERSION;
$checks['PHP Version'] = version_compare($phpv, '8.0', '>=') 
    ? ['ok', $phpv] 
    : ['err', "$phpv (precisa 8.0+)"];

// ─── Extensions ───
foreach (['pdo_sqlite', 'mbstring', 'json', 'fileinfo'] as $ext) {
    $checks["Extensão $ext"] = extension_loaded($ext)
        ? ['ok', 'OK']
        : ['err', 'FALTA — habilite no cPanel → Select PHP Version'];
}

// ─── .env ───
$envFile = "$root/.env";
$checks['Arquivo .env'] = file_exists($envFile)
    ? ['ok', 'Existe']
    : ['warn', 'NÃO encontrado (será usado .env.example ou defaults)'];

// ─── .htaccess ───
$htFile = "$root/.htaccess";
$checks['.htaccess raiz'] = file_exists($htFile)
    ? ['ok', 'Existe']
    : ['err', 'NÃO encontrado — rotas não funcionarão'];

if (file_exists($htFile)) {
    $htContent = file_get_contents($htFile);
    if (preg_match('/RewriteBase\s+(\S+)/', $htContent, $m)) {
        $checks['RewriteBase'] = ['info', $m[1] . ' — verifique se é o caminho correto no cPanel'];
    }
}

// ─── Database dir writable ───
$dbDir = "$root/database";
$checks['database/ existe'] = is_dir($dbDir) ? ['ok', 'Sim'] : ['err', 'NÃO — crie a pasta'];
$checks['database/ escrevível'] = is_writable($dbDir) ? ['ok', 'Sim'] : ['err', 'NÃO — chmod 755 ou 777'];

// ─── Logs dir writable ───
$logDir = "$root/logs";
if (!is_dir($logDir)) @mkdir($logDir, 0755, true);
$checks['logs/ escrevível'] = is_writable($logDir) ? ['ok', 'Sim'] : ['err', 'NÃO — chmod 755'];

// ─── mod_rewrite check ───
$checks['mod_rewrite'] = function_exists('apache_get_modules') && in_array('mod_rewrite', apache_get_modules())
    ? ['ok', 'Ativo']
    : ['warn', 'Não detectado — se  404, verifique no cPanel'];

// ─── Database seed ───
$seeded = false;
$seedResults = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['seed'])) {
    try {
        require_once "$root/config.php";
        $pdo = getPDO();
        
        foreach (['database/migration.sql', 'database/seed.sql'] as $file) {
            $sql = file_get_contents("$root/$file");
            $pdo->exec($sql);
            $seedResults[$file] = ['ok', 'Executado'];
        }
        $seeded = true;
    } catch (Exception $e) {
        $seedResults['Erro'] = ['err', $e->getMessage()];
    }
}

// ─── Teste de roteamento ───
$baseHref = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');

function icon($type) {
    return ['ok' => '✓', 'err' => '✗', 'warn' => '⚠', 'info' => 'ℹ'][$type] ?? '?';
}
function color($type) {
    return ['ok' => '#22c55e', 'err' => '#ef4444', 'warn' => '#f59e0b', 'info' => '#3b82f6'][$type] ?? '#94a3b8';
}
?>
<!DOCTYPE html>
<html lang="pt">
<head>
<meta charset="utf-8">
<title>Setup — ProFoxTruss</title>
<meta name="viewport" content="width=device-width,initial-scale=1">
<style>
*{box-sizing:border-box}
body{font-family:system-ui,sans-serif;background:#0f172a;color:#e2e8f0;padding:1rem}
.card{background:#1e293b;border:1px solid #334155;border-radius:12px;padding:1.5rem;max-width:600px;margin:0 auto}
h1{font-size:1.25rem;margin:0 0 1.5rem;color:#38bdf8}
h2{font-size:.9rem;color:#94a3b8;margin:1.5rem 0 .75rem;text-transform:uppercase;letter-spacing:.05em}
.row{display:flex;justify-content:space-between;align-items:center;padding:.45rem 0;border-bottom:1px solid #1e293b;font-size:.85rem}
.row .icon{font-weight:700;margin-right:.5rem}
.row .val{text-align:right;max-width:55%;word-break:break-all}
form{margin-top:1rem}
button{background:#22c55e;color:#fff;border:none;padding:.6rem 1.5rem;border-radius:8px;font-weight:700;font-size:.9rem;cursor:pointer}
button:hover{background:#16a34a}
.note{font-size:.72rem;color:#64748b;margin-top:1.25rem;line-height:1.5}
.note a{color:#38bdf8}
</style>
</head>
<body>
<div class="card">
<h1>⚙ ProFoxTruss — Diagnóstico & Setup</h1>

<h2>1. Ambiente</h2>
<?php foreach ($checks as $label => [$type, $val]): ?>
<div class="row">
<span><span class="icon" style="color:<?=color($type)?>"><?=icon($type)?></span> <?=$label?></span>
<span class="val"><?=htmlspecialchars($val)?></span>
</div>
<?php endforeach; ?>

<h2>2. Banco de Dados</h2>
<?php if (!empty($seedResults)): ?>
<?php foreach ($seedResults as $file => [$type, $val]): ?>
<div class="row">
<span><span class="icon" style="color:<?=color($type)?>"><?=icon($type)?></span> <?=$file?></span>
<span class="val"><?=htmlspecialchars($val)?></span>
</div>
<?php endforeach; ?>
<?php endif; ?>
<?php if (!$seeded): ?>
<form method="post">
<button type="submit" name="seed" value="1">▶ Rodar Migration + Seed</button>
</form>
<?php else: ?>
<div class="row"><span class="icon" style="color:#22c55e">✓</span> Banco pronto</div>
<?php endif; ?>

<?php if (!empty($errors)): ?>
<h2>3. Problemas a corrigir</h2>
<?php foreach ($errors as $e): ?>
<div class="row"><span style="color:#ef4444"><?=htmlspecialchars($e)?></span></div>
<?php endforeach; ?>
<?php endif; ?>

<p class="note">
<strong>Após tudo verde:</strong> acesse <a href="<?=$baseHref?>/"><?=$baseHref?>/</a> para abrir o sistema.<br>
Se ainda der 404: verifique se o <code>RewriteBase</code> no <code>.htaccess</code> da raiz é <code><?=$baseHref?></code>.<br>
<strong>Delete este arquivo</strong> (<code>setup.php</code>) após tudo funcionar.
</p>
</div>
</body>
</html>
