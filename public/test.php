<?php
/**
 * test.php — Teste mínimo do bootstrap. Acesse: /profoxtruss/public/test.php
 * Delete após uso.
 */
error_reporting(E_ALL);
ini_set('display_errors', '1');

define('ROOT', dirname(__DIR__));

echo "<pre>ROOT: " . ROOT . "\n";

// 1. Config
try {
    require ROOT . '/config.php';
    echo "✓ config.php\n";
} catch (Throwable $e) {
    die("✗ config.php: " . $e->getMessage());
}

// 2. PDO / DB
try {
    $pdo = getPDO();
    $pdo->query("SELECT 1");
    echo "✓ Banco SQLite OK\n";
    
    $count = $pdo->query("SELECT COUNT(*) FROM produtos")->fetchColumn();
    echo "✓ Produtos no banco: $count\n";
    
    $users = $pdo->query("SELECT email, role FROM users LIMIT 1")->fetch();
    if ($users) echo "✓ Usuário: {$users['email']} ({$users['role']}) — senha: admin123\n";
    else echo "⚠ Nenhum usuário\n";
} catch (Throwable $e) {
    die("✗ Banco: " . $e->getMessage());
}

// 3. Helpers
try {
    require ROOT . '/app/helpers.php';
    echo "✓ helpers.php\n";
} catch (Throwable $e) {
    die("✗ helpers.php: " . $e->getMessage());
}

// 4. Autoload
spl_autoload_register(function (string $class): void {
    foreach ([
        ROOT . '/app/controllers/',
        ROOT . '/app/models/',
        ROOT . '/app/repositories/',
        ROOT . '/app/services/',
        ROOT . '/app/middlewares/',
        ROOT . '/app/utils/',
    ] as $dir) {
        $file = $dir . $class . '.php';
        if (file_exists($file)) { require_once $file; return; }
    }
});

$required = ['ErrorHandler', 'Router', 'HomeController', 'ApiController', 'PecaController', 'Env', 'Layout'];
foreach ($required as $cls) {
    echo class_exists($cls) ? "✓ $cls\n" : "✗ $cls NÃO ENCONTRADO\n";
}

// 5. Routes
try {
    require ROOT . '/routes.php';
    echo "✓ routes.php\n";
} catch (Throwable $e) {
    die("✗ routes.php: " . $e->getMessage());
}

// 6. Session
if (session_status() === PHP_SESSION_ACTIVE) {
    echo "✓ Sessão ativa\n";
}

// 7. File permissions check
echo "\n--- Permissões ---\n";
$checkPerms = [
    'database/database.sqlite' => 'Arquivo DB',
    'logs/' => 'Pasta logs',
    'database/' => 'Pasta database',
];
foreach ($checkPerms as $path => $label) {
    $full = ROOT . '/' . $path;
    $perms = file_exists($full) ? substr(sprintf('%o', fileperms($full)), -4) : 'NÃO EXISTE';
    $writable = is_writable($full) ? 'writable' : 'NÃO writable';
    echo "$label ($path): $perms $writable\n";
}

echo "\n✓ Bootstrap completo sem erros!</pre>";
