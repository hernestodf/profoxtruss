<?php

Router::get('/login',    ['AuthController', 'login']);
Router::post('/login',   ['AuthController', 'doLogin']);
Router::get('/logout',   ['AuthController', 'logout']);


