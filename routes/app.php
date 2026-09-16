<?php

Router::get('/', ['HomeController', 'index'], ['Auth']);
Router::get('/dashboard', ['DashController', 'index'], ['Auth']);


