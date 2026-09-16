<?php

Router::group('/admin', ['Auth'], function () {

    Router::get('',         ['AdminController', 'index']);
    Router::get('/reports', ['AdminController', 'reports']);

    Router::group('/users', ['Rbac:user.view'], function () {
        Router::get('',            ['AdminController', 'users']);
        Router::get('/{id}',       ['AdminController', 'showUser']);
        Router::get('/{id}/edit',  ['AdminController', 'editUser']);
        Router::post('/{id}',      ['AdminController', 'updateUser']);
        Router::delete('/{id}',    ['AdminController', 'deleteUser']);
    });

    // Gerenciamento de permissões via painel
    Router::group('/permissions', ['Rbac:admin.view'], function () {
        Router::get('',        ['AdminController', 'permissions']);
        Router::post('/grant', ['AdminController', 'grantPermission']);
        Router::post('/revoke',['AdminController', 'revokePermission']);
    });

});


