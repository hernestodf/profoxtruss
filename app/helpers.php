<?php

// ── Navegação ──────────────────────────────────────────────────────────────────

function redirect(string $url): never
{
    header('Location: ' . url($url));
    exit;
}

function url(string $path = ''): string
{
    // Se o caminho já for uma URL completa
    if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
        return $path;
    }
    
    // Normaliza removendo barras iniciais
    $cleanPath = ltrim($path, '/');
    
    // Se o caminho já vier prefixado com o BASE_PATH, remove para evitar duplicação
    if (BASE_PATH !== '' && str_starts_with('/' . $cleanPath, BASE_PATH . '/')) {
        $cleanPath = substr('/' . $cleanPath, strlen(BASE_PATH) + 1);
        $cleanPath = ltrim($cleanPath, '/');
    }
    
    // Se já vier com index.php, normaliza
    if (str_starts_with($cleanPath, 'index.php/')) {
        $cleanPath = substr($cleanPath, 10);
    } elseif ($cleanPath === 'index.php') {
        $cleanPath = '';
    }
    
    // Detecta se estamos rodando em modo fallback (URL explicitamente com index.php)
    $useIndexPhp = false;
    if (isset($_SERVER['REQUEST_URI']) && str_contains($_SERVER['REQUEST_URI'], '/index.php')) {
        if (!str_starts_with($cleanPath, 'assets/') && !str_starts_with($cleanPath, 'favicon.ico')) {
            $useIndexPhp = true;
        }
    }
    
    if ($useIndexPhp) {
        return BASE_PATH . '/index.php/' . $cleanPath;
    }
    
    return BASE_PATH . '/' . $cleanPath;
}

// ── Debug ──────────────────────────────────────────────────────────────────────

function dd(mixed ...$vars): never
{
    echo '<pre style="background:#1e1e1e;color:#d4d4d4;padding:16px;border-radius:8px;font-size:13px;white-space:pre-wrap">';
    foreach ($vars as $var) {
        var_dump($var);
        echo PHP_EOL;
    }
    echo '</pre>';
    exit;
}

// ── Views ──────────────────────────────────────────────────────────────────────

function view(string $template, array $data = []): void
{
    $path = ROOT . '/app/views/' . $template . '.php';

    if (!file_exists($path)) {
        appLog('error', "View não encontrada: {$template}", __FILE__, __LINE__);
        http_response_code(500);
        die('Erro interno: view não encontrada.');
    }

    Layout::render($path, $data);
}

function component(string $name, array $data = []): void
{
    $path = ROOT . '/app/views/components/' . $name . '.php';
    if (!file_exists($path)) {
        appLog('warning', "Componente não encontrado: {$name}", __FILE__, __LINE__);
        return;
    }
    extract($data, EXTR_SKIP);
    require $path;
}

// ── Formulários ────────────────────────────────────────────────────────────────

function post(string $key, string $default = ''): string
{
    return htmlspecialchars(trim($_POST[$key] ?? $default), ENT_QUOTES, 'UTF-8');
}

function get(string $key, string $default = ''): string
{
    return htmlspecialchars(trim($_GET[$key] ?? $default), ENT_QUOTES, 'UTF-8');
}

function old(string $key, string $default = ''): string
{
    return htmlspecialchars(trim($_POST[$key] ?? $default), ENT_QUOTES, 'UTF-8');
}

// ── Sessão ─────────────────────────────────────────────────────────────────────

function isLoggedIn(): bool
{
    return isset($_SESSION['user_id']);
}

function userRole(): string
{
    return $_SESSION['user_role'] ?? '';
}

function csrfToken(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrfValidate(): void
{
    $token = $_POST['csrf_token'] ?? '';
    if (!hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
        appLog('warning', 'CSRF token inválido', '', 0, [
            'ip'  => $_SERVER['REMOTE_ADDR'] ?? '?',
            'uri' => $_SERVER['REQUEST_URI'] ?? '?',
        ]);
        http_response_code(403);
        die('Requisição inválida.');
    }
}

/**
 * Mesma checagem de csrfValidate(), mas pra endpoints JSON (ApiController):
 * eles não têm $_POST (o body é JSON puro), então o token vem por um header
 * próprio (X-CSRF-Token) em vez de campo de formulário. Sem isto, qualquer
 * um dos métodos POST/DELETE de app/controllers/ApiController.php aceitava
 * a requisição só com a sessão da vítima "andando junto" — nenhum token era
 * checado — e como getJsonInput() faz json_decode() sem olhar Content-Type,
 * um POST cross-site com Content-Type: text/plain (não dispara preflight
 * CORS) e corpo JSON válido passava direto. Responde JSON (não die() com
 * HTML) porque quem chama espera `{ok, ...}`.
 */
function csrfValidateJson(): void
{
    $token = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
    if (!hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
        appLog('warning', 'CSRF token inválido (API JSON)', '', 0, [
            'ip'  => $_SERVER['REMOTE_ADDR'] ?? '?',
            'uri' => $_SERVER['REQUEST_URI'] ?? '?',
        ]);
        http_response_code(403);
        header('Content-Type: application/json');
        echo json_encode(['ok' => false, 'error' => 'Requisição inválida (CSRF).']);
        exit;
    }
}

// ── Log ────────────────────────────────────────────────────────────────────────

/**
 * Grava uma linha estruturada em logs/app.log.
 *
 * Formato:
 *   [2024-01-15 14:32:01] [ERROR] Mensagem | file.php:42 | user:7 ip:192.168.0.1
 *   [2024-01-15 14:32:01] [ERROR] Mensagem | file.php:42 | cpf=111.444.777-35
 *
 * Níveis: debug | info | warning | error
 *
 * Uso:
 *   appLog('info',    'Cliente criado', __FILE__, __LINE__, ['id' => 5]);
 *   appLog('warning', 'Login falhou',   __FILE__, __LINE__, ['email' => $email]);
 *   appLog('error',   'Falha no banco', __FILE__, __LINE__);
 */
function appLog(
    string $level,
    string $message,
    string $file    = '',
    int    $line    = 0,
    array  $context = []
): void {
    // Contexto automático de sessão
    if (isset($_SESSION['user_id'])) {
        $context = array_merge(['user' => $_SESSION['user_id']], $context);
    }

    $context['ip'] = $_SERVER['REMOTE_ADDR'] ?? 'cli';

    // Monta string de contexto: key:value key:value
    $ctx = implode(' ', array_map(
        fn($k, $v) => "{$k}:{$v}",
        array_keys($context),
        array_values($context)
    ));

    // Localização: só o nome do arquivo, sem path absoluto
    $loc = $file ? basename($file) . ":{$line}" : '';

    $parts = array_filter([
        sprintf('[%s] [%s] %s', date('Y-m-d H:i:s'), strtoupper($level), $message),
        $loc,
        $ctx,
    ]);

    error_log(implode(' | ', $parts));
}


