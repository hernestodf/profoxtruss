<?php

Router::group('/pecas', ['Auth'], function () {
    Router::get('', ['PecaController', 'index']);
});
