<?php

class PecaController extends BaseController
{
    public function index(): void
    {
        // Se estiver autenticado, permite acesso
        if (!isLoggedIn()) {
            $this->redirect('/login');
        }
        
        $this->view('pecas/index', ['title' => 'Peças & Estoque']);
    }
}
