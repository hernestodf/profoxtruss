<?php
// Fallback de DirectoryIndex: o Apache só chega aqui para a URL raiz EXATA
// (`/profoxtruss/`, com barra final) — o RewriteCond `!-d` do .htaccess
// pula o rewrite quando o caminho é um diretório real, então essa é a
// única requisição que não passa pelo pipeline normal via public/index.php.
// Antes redirecionava incondicionalmente para /login, inclusive para quem
// já estava autenticado (bug: sessão ignorada). Delega para o front
// controller real, igual a qualquer outra rota.
require __DIR__ . '/public/index.php';
