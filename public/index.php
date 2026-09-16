<?php

declare(strict_types=1);

define('ROOT', dirname(__DIR__));

require ROOT . '/config.php';
require ROOT . '/app/helpers.php';

// ─── Autoload ─────────────────────────────────────────────────────────────────
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

// ─── Tratamento global de erros ───────────────────────────────────────────────
ErrorHandler::register();

// ─── Calcula o BASE_PATH dinamicamente ─────────────────────────────────────────
$scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
$basePath = dirname($scriptName);
if ($basePath === '/' || $basePath === '\\') {
    $basePath = '';
}
define('BASE_PATH', $basePath);

// ─── Registra todas as rotas ──────────────────────────────────────────────────
require ROOT . '/routes.php';

// ─── Resolve URI removendo o BASE_PATH do início ──────────────────────────────
//
// Exemplo em subpasta:
//   REQUEST_URI = /meuprojeto/public/admin/users
//   BASE_PATH   = /meuprojeto/public
//   URI final   = /admin/users   ← o Router só vê isso
//
$requestUri = $_SERVER['REQUEST_URI'] ?? '/';

// Tenta remover o BASE_PATH completo (ex: /profoxtruss/public)
if (BASE_PATH !== '' && str_starts_with($requestUri, BASE_PATH)) {
    $requestUri = substr($requestUri, strlen(BASE_PATH));
}

// Fallback: quando o .htaccess da raiz do projeto redireciona para public/index.php,
// o SCRIPT_NAME contém /public/ mas a URL original não.
// Nesse caso, tenta remover o diretório pai do BASE_PATH (ex: /profoxtruss).
if (BASE_PATH !== '' && str_starts_with($requestUri, '/')) {
    $parentBase = dirname(BASE_PATH);
    if ($parentBase !== '' && $parentBase !== '/' && str_starts_with($requestUri, $parentBase)) {
        $requestUri = substr($requestUri, strlen($parentBase));
    }
}

// Fallback para quando o mod_rewrite do Apache não está ativo (URL com index.php)
if (str_starts_with($requestUri, '/index.php')) {
    $requestUri = substr($requestUri, 10);
}

$requestUri = $requestUri ?: '/';

// ─── Match ────────────────────────────────────────────────────────────────────
$match = Router::match($_SERVER['REQUEST_METHOD'], $requestUri);

if (!$match) {
    ErrorHandler::notFound();
}

[$controllerClass, $method, $middlewares, $params] = $match;

$GLOBALS['_ROUTE_PARAMS'] = $params;

// ─── Pipeline de middlewares ──────────────────────────────────────────────────
foreach ($middlewares as $middleware) {
    if (str_contains($middleware, ':')) {
        [$mClass, $param] = explode(':', $middleware, 2);
        $mClass::handle($param);
    } else {
        $middleware::handle();
    }
}

// ─── Executa o controller ─────────────────────────────────────────────────────
if (!class_exists($controllerClass) || !method_exists($controllerClass, $method)) {
    ErrorHandler::serverError("Controller não encontrado: {$controllerClass}::{$method}");
}

(new $controllerClass())->$method();


