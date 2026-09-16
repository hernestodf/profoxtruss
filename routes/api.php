<?php

/**
 * Rotas de API do ProFoxTruss.
 */
Router::group('/api', ['Auth'], function () {
    // Componentes / Peças / Estoque
    Router::get('/pecas',           ['ApiController', 'listarPecas']);
    Router::post('/pecas',          ['ApiController', 'salvarPeca']);
    Router::delete('/pecas/{id}',   ['ApiController', 'deletarPeca']);

    // Projetos de Box Truss
    Router::get('/projects',         ['ApiController', 'listarProjetos']);
    Router::get('/projects/{id}',    ['ApiController', 'getProjeto']);
    Router::post('/projects',        ['ApiController', 'salvarProjeto']);
    Router::delete('/projects/{id}', ['ApiController', 'deletarProjeto']);
});
