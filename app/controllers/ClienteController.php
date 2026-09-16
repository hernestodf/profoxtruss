<?php

class ClienteController extends BaseController
{
    public function __construct(
        private ClienteRepository $clientes = new ClienteRepository()
    ) {}

    public function index(): void
    {
        $this->authorize('cliente.view');
        $this->view('clientes/index', [
            'clientes' => $this->clientes->all(),
        ]);
    }

    public function novo(): void
    {
        $this->authorize('cliente.create');
        $this->view('clientes/form', ['cliente' => null]);
    }

    public function store(): void
    {
        $this->authorize('cliente.create');
        $this->csrf();

        [$erros, $nome, $cpf] = $this->validar();

        if ($erros) {
            $this->view('clientes/form', ['cliente' => null, 'erros' => $erros]);
            return;
        }

        if ($this->clientes->findByCpf($cpf)) {
            appLog('warning', 'Tentativa de cadastro com CPF duplicado', __FILE__, __LINE__, [
                'cpf' => $cpf,
            ]);
            $this->view('clientes/form', [
                'cliente' => null,
                'erros'   => ['cpf' => 'Este CPF já está cadastrado.'],
            ]);
            return;
        }

        $id = $this->clientes->create($nome, $cpf);
        appLog('info', 'Cliente criado', __FILE__, __LINE__, ['cliente_id' => $id, 'cpf' => $cpf]);
        $this->redirect('/clientes?ok=criado');
    }

    public function editar(): void
    {
        $this->authorize('cliente.edit');
        $cliente = $this->clientes->find((int) $this->param('id'));
        if (!$cliente) $this->notFound();
        $this->view('clientes/form', ['cliente' => $cliente]);
    }

    public function update(): void
    {
        $this->authorize('cliente.edit');
        $this->csrf();

        $id      = (int) $this->param('id');
        $cliente = $this->clientes->find($id);
        if (!$cliente) $this->notFound();

        [$erros, $nome, $cpf] = $this->validar();

        if ($erros) {
            $this->view('clientes/form', ['cliente' => $cliente, 'erros' => $erros]);
            return;
        }

        $existente = $this->clientes->findByCpf($cpf);
        if ($existente && (int) $existente['id'] !== $id) {
            appLog('warning', 'Tentativa de atualização com CPF de outro cliente', __FILE__, __LINE__, [
                'cliente_id'   => $id,
                'cpf_conflito' => $cpf,
            ]);
            $this->view('clientes/form', [
                'cliente' => $cliente,
                'erros'   => ['cpf' => 'Este CPF já está cadastrado para outro cliente.'],
            ]);
            return;
        }

        $this->clientes->update($id, $nome, $cpf);
        appLog('info', 'Cliente atualizado', __FILE__, __LINE__, ['cliente_id' => $id]);
        $this->redirect('/clientes?ok=atualizado');
    }

    public function delete(): void
    {
        $this->authorize('cliente.delete');
        $this->csrf();

        $id = (int) $this->param('id');
        if (!$this->clientes->find($id)) $this->notFound();

        $this->clientes->delete($id);
        appLog('info', 'Cliente excluído', __FILE__, __LINE__, ['cliente_id' => $id]);
        $this->redirect('/clientes?ok=deletado');
    }

    // ── Validação ─────────────────────────────────────────────────────────────

    private function validar(): array
    {
        $erros = [];
        $nome  = trim(post('nome'));
        $cpf   = post('cpf');

        if ($nome === '') {
            $erros['nome'] = 'O nome é obrigatório.';
        } elseif (mb_strlen($nome) < 3) {
            $erros['nome'] = 'O nome deve ter ao menos 3 caracteres.';
        } elseif (mb_strlen($nome) > 100) {
            $erros['nome'] = 'O nome deve ter no máximo 100 caracteres.';
        }

        if ($cpf === '') {
            $erros['cpf'] = 'O CPF é obrigatório.';
        } elseif (!$this->cpfValido($cpf)) {
            $erros['cpf'] = 'CPF inválido.';
        }

        return [$erros, $nome, $cpf];
    }

    private function cpfValido(string $cpf): bool
    {
        $n = preg_replace('/\D/', '', $cpf);
        if (strlen($n) !== 11 || preg_match('/^(\d)\1{10}$/', $n)) return false;

        for ($t = 9; $t < 11; $t++) {
            $soma = 0;
            for ($i = 0; $i < $t; $i++) $soma += (int) $n[$i] * ($t + 1 - $i);
            $r = (10 * $soma) % 11;
            if ((int) $n[$t] !== ($r > 9 ? 0 : $r)) return false;
        }
        return true;
    }
}


