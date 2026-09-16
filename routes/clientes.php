<?php

Router::group('/clientes', ['Auth'], function () {
    Router::get('',              ['ClienteController', 'index']);
    Router::get('/novo',         ['ClienteController', 'novo']);
    Router::post('',             ['ClienteController', 'store']);
    Router::get('/{id}/editar',  ['ClienteController', 'editar']);
    Router::post('/{id}',        ['ClienteController', 'update']);
    Router::post('/{id}/deletar',['ClienteController', 'delete']);
});


