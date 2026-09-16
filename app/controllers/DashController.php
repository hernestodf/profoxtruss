<?php

class DashController extends BaseController
{
    public function index(): void
    {
        $this->authorize('dashboard.view');
        
        try {
            $db = getPDO();
            
            // Fetch stats from SQLite
            $totalProjetos = $db->query('SELECT COUNT(*) FROM projects')->fetchColumn();
            $totalPecas = $db->query('SELECT COUNT(*) FROM produtos WHERE ativo = 1')->fetchColumn();
            $totalEstoque = $db->query('SELECT SUM(estoque) FROM produtos WHERE ativo = 1')->fetchColumn() ?: 0;
            $itensFalta = $db->query('SELECT COUNT(*) FROM produtos WHERE estoque = 0 AND ativo = 1')->fetchColumn();
            
            // Recent saved projects
            $projetosRecentes = $db->query('SELECT * FROM projects ORDER BY created_at DESC LIMIT 5')->fetchAll();
            
            // Critical stock components (estoque < 15)
            $pecasEstoqueBaixo = $db->query('SELECT * FROM produtos WHERE estoque < 15 AND ativo = 1 ORDER BY estoque ASC LIMIT 5')->fetchAll();
        } catch (Exception $e) {
            $totalProjetos = 0;
            $totalPecas = 0;
            $totalEstoque = 0;
            $itensFalta = 0;
            $projetosRecentes = [];
            $pecasEstoqueBaixo = [];
        }

        $this->view('dash/index', [
            'title'             => 'Dashboard Geral',
            'userName'          => $_SESSION['user_name'],
            'userRole'          => $_SESSION['user_role'],
            'totalProjetos'     => $totalProjetos,
            'totalPecas'        => $totalPecas,
            'totalEstoque'      => $totalEstoque,
            'itensFalta'        => $itensFalta,
            'projetosRecentes'  => $projetosRecentes,
            'pecasEstoqueBaixo' => $pecasEstoqueBaixo
        ]);
    }
}
