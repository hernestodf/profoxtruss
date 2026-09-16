<?php
/**
 * Router script para `php -S` (servidor embutido, usado em CI/local sem
 * Apache — ver .github/workflows/playwright.yml). Reproduz o que o
 * .htaccess de public/ faz via mod_rewrite: serve arquivos estáticos
 * reais como estão, tudo o mais cai em index.php.
 */
$path = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? '/');
$file = __DIR__ . $path;

if ($path !== '/' && is_file($file)) {
    return false;
}

require __DIR__ . '/index.php';
