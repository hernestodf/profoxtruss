<?php

class HomeController extends BaseController
{
    public function index(): void
    {
        $this->view('home/index', ['title' => 'Início']);
    }
}


