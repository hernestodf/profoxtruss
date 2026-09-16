<?php

Router::group('/pedidos', ['Auth'], function () {
    Router::get('',               ['PedidoController', 'index']);
    Router::get('/novo',          ['PedidoController', 'novo']);
    Router::post('',              ['PedidoController', 'store']);
    Router::get('/{id}/editar',   ['PedidoController', 'editar']);
    Router::post('/{id}',         ['PedidoController', 'update']);
    Router::post('/{id}/deletar', ['PedidoController', 'delete']);
});

// Endpoints JSON — Auth obrigatório, sem RBAC extra (já está no controller)
Router::group('/api', ['Auth'], function () {
    Router::get('/clientes/busca',  ['ApiController', 'buscarClientes']);
    Router::get('/produtos/busca',  ['ApiController', 'buscarProdutos']);
    Router::get('/produtos/{id}',   ['ApiController', 'getProduto']);
});


