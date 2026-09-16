<?php

/**
 * Ponto central de rotas.
 * Cada arquivo registra suas rotas diretamente no Router estático.
 * Para novo módulo: crie routes/modulo.php e inclua aqui.
 */
require __DIR__ . '/routes/app.php';
require __DIR__ . '/routes/auth.php';
require __DIR__ . '/routes/admin.php';
// require __DIR__ . '/routes/clientes.php';
// require __DIR__ . '/routes/pedidos.php';
require __DIR__ . '/routes/pecas.php';
require __DIR__ . '/routes/api.php';


