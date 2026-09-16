<?php

/**
 * Endpoints JSON para requisições AJAX do ProFoxTruss.
 */
class ApiController extends BaseController
{
    private function getJsonInput(): array
    {
        $input = json_decode(file_get_contents('php://input'), true);
        return $input ?: [];
    }

    // ── Módulo de Clientes (Compatibilidade) ───────────────────────────────────

    public function buscarClientes(): void
    {
        $this->authorize('cliente.view');

        $q = trim(get('q'));
        if (strlen($q) < 2) {
            $this->json(['ok' => true, 'data' => []]);
        }

        $repo      = new ClienteRepository();
        $clientes  = $repo->search($q);

        $this->json([
            'ok'   => true,
            'data' => array_map(fn($c) => [
                'id'    => $c['id'],
                'label' => $c['nome'] . ' — ' . $c['cpf'],
                'nome'  => $c['nome'],
                'cpf'   => $c['cpf'],
            ], $clientes),
        ]);
    }

    // ── Módulo de Peças / Catálogo de Componentes e Estoque ───────────────────

    // GET /api/pecas
    public function listarPecas(): void
    {
        // Se estiver autenticado, permite acesso
        if (!isLoggedIn()) {
            $this->json(['ok' => false, 'error' => 'Não autorizado'], 401);
        }

        $repo = new ProdutoRepository();
        $pecas = $repo->all(false); // Retorna ativas e inativas para gerenciamento de estoque

        $this->json([
            'ok' => true,
            'data' => $pecas
        ]);
    }

    // POST /api/pecas (salvar/criar/editar)
    public function salvarPeca(): void
    {
        $this->authorize('admin.view'); // Apenas admin gerencia estoque

        $input = $this->getJsonInput();
        $id = isset($input['id']) ? (int) $input['id'] : null;

        if (empty($input['codigo']) || empty($input['nome']) || empty($input['tipo'])) {
            $this->json(['ok' => false, 'error' => 'Código, nome e tipo são obrigatórios.'], 400);
        }

        $repo = new ProdutoRepository();

        $data = [
            'codigo'      => sanitize($input['codigo']),
            'nome'        => sanitize($input['nome']),
            'tipo'        => sanitize($input['tipo']),
            'comprimento' => (float) ($input['comprimento'] ?? 0),
            'peso'        => (float) ($input['peso'] ?? 0),
            'preco'       => (float) ($input['preco'] ?? 0),
            'estoque'     => (int) ($input['estoque'] ?? 0),
            'ativo'       => (int) ($input['ativo'] ?? 1),
            'cor'         => sanitize($input['cor'] ?? '')
        ];

        try {
            if ($id) {
                $success = $repo->update($id, $data);
                $this->json(['ok' => $success, 'data' => ['id' => $id]]);
            } else {
                // Verificar se código já existe
                $exists = $repo->findByCodigo($data['codigo']);
                if ($exists) {
                    $this->json(['ok' => false, 'error' => 'Código de componente já cadastrado.'], 400);
                }
                $newId = $repo->create($data);
                $this->json(['ok' => true, 'data' => ['id' => $newId]]);
            }
        } catch (Exception $e) {
            $this->json(['ok' => false, 'error' => $e->getMessage()], 500);
        }
    }

    // DELETE /api/pecas/{id}
    public function deletarPeca(): void
    {
        $this->authorize('admin.view');
        $id = (int) $this->param('id');

        $repo = new ProdutoRepository();
        $peca = $repo->find($id);
        if (!$peca) {
            $this->json(['ok' => false, 'error' => 'Componente não encontrado.'], 404);
        }

        try {
            $success = $repo->delete($id);
            $this->json(['ok' => $success]);
        } catch (Exception $e) {
            $this->json(['ok' => false, 'error' => 'Não é possível deletar esta peça pois ela pode estar em uso por pedidos ou projetos.'], 500);
        }
    }

    // ── Módulo de Projetos de Treliças Box Truss ──────────────────────────────

    // GET /api/projects
    public function listarProjetos(): void
    {
        $this->authorize('project.view');

        $repo = new ProjectRepository();
        $projects = $repo->all();

        $this->json([
            'ok' => true,
            'data' => $projects
        ]);
    }

    // GET /api/projects/{id}
    public function getProjeto(): void
    {
        $this->authorize('project.view');
        $id = (int) $this->param('id');

        $repo = new ProjectRepository();
        $project = $repo->find($id);

        if (!$project) {
            $this->json(['ok' => false, 'error' => 'Projeto não encontrado.'], 404);
        }

        $this->json([
            'ok' => true,
            'data' => $project
        ]);
    }

    // POST /api/projects
    public function salvarProjeto(): void
    {
        $this->authorize('project.create');

        $input = $this->getJsonInput();
        $id = isset($input['id']) ? (int) $input['id'] : null;

        if (empty($input['name']) || empty($input['components'])) {
            $this->json(['ok' => false, 'error' => 'Nome do projeto e componentes do canvas são obrigatórios.'], 400);
        }

        $repo = new ProjectRepository();

        $data = [
            'name'       => sanitize($input['name']),
            'width'      => (float) ($input['width'] ?? 0),
            'height'     => (float) ($input['height'] ?? 0),
            'length'     => (float) ($input['length'] ?? 0),
            'scale'      => (int) ($input['scale'] ?? 50),
            'components' => $input['components'] // String JSON de componentes
        ];

        try {
            if ($id) {
                $this->authorize('project.edit');
                $success = $repo->update($id, $data);
                $this->json(['ok' => $success, 'data' => ['id' => $id]]);
            } else {
                $newId = $repo->create($data);
                $this->json(['ok' => true, 'data' => ['id' => $newId]]);
            }
        } catch (Exception $e) {
            $this->json(['ok' => false, 'error' => $e->getMessage()], 500);
        }
    }

    // DELETE /api/projects/{id}
    public function deletarProjeto(): void
    {
        $this->authorize('project.delete');
        $id = (int) $this->param('id');

        $repo = new ProjectRepository();
        $project = $repo->find($id);

        if (!$project) {
            $this->json(['ok' => false, 'error' => 'Projeto não encontrado.'], 404);
        }

        $success = $repo->delete($id);
        $this->json(['ok' => $success]);
    }
}

// Função auxiliar simples para higienização
function sanitize($value) {
    return htmlspecialchars(trim((string)$value), ENT_QUOTES, 'UTF-8');
}
