<?php

class PedidoController extends BaseController
{
    public function __construct(
        private PedidoRepository  $pedidos  = new PedidoRepository(),
        private ClienteRepository $clientes = new ClienteRepository(),
    ) {}

    // GET /pedidos
    public function index(): void
    {
        $this->authorize('pedido.view');
        $this->view('pedidos/index', [
            'pedidos' => $this->pedidos->all(),
        ]);
    }

    // GET /pedidos/novo
    public function novo(): void
    {
        $this->authorize('pedido.create');
        $this->view('pedidos/form', ['pedido' => null, 'itens' => []]);
    }

    // POST /pedidos
    public function store(): void
    {
        $this->authorize('pedido.create');
        $this->csrf();

        [$erros, $dados] = $this->validar();
        if ($erros) {
            $this->view('pedidos/form', ['pedido' => null, 'itens' => [], 'erros' => $erros]);
            return;
        }

        $id = $this->pedidos->create(
            $dados['cliente_id'],
            $dados['desconto'],
            $dados['observacao'],
            $dados['itens']
        );

        appLog('info', "pedido #{$id} criado por user_id={$_SESSION['user_id']}");
        $this->redirect('/pedidos?ok=criado');
    }

    // GET /pedidos/{id}/editar
    public function editar(): void
    {
        $this->authorize('pedido.edit');

        $id     = (int) $this->param('id');
        $pedido = $this->pedidos->find($id);
        if (!$pedido) $this->notFound();

        $this->view('pedidos/form', [
            'pedido' => $pedido,
            'itens'  => $this->pedidos->itens($id),
        ]);
    }

    // POST /pedidos/{id}
    public function update(): void
    {
        $this->authorize('pedido.edit');
        $this->csrf();

        $id     = (int) $this->param('id');
        $pedido = $this->pedidos->find($id);
        if (!$pedido) $this->notFound();

        [$erros, $dados] = $this->validar();
        if ($erros) {
            $this->view('pedidos/form', ['pedido' => $pedido, 'itens' => $this->pedidos->itens($id), 'erros' => $erros]);
            return;
        }

        $this->pedidos->update($id, $dados['cliente_id'], $dados['desconto'], $dados['observacao'], $dados['itens']);
        appLog('info', "pedido #{$id} atualizado por user_id={$_SESSION['user_id']}");
        $this->redirect('/pedidos?ok=atualizado');
    }

    // POST /pedidos/{id}/deletar
    public function delete(): void
    {
        $this->authorize('pedido.delete');
        $this->csrf();

        $id = (int) $this->param('id');
        if (!$this->pedidos->find($id)) $this->notFound();

        $this->pedidos->delete($id);
        appLog('info', "pedido #{$id} deletado por user_id={$_SESSION['user_id']}");
        $this->redirect('/pedidos?ok=deletado');
    }

    // ── Validação ─────────────────────────────────────────────────────────────

    private function validar(): array
    {
        $erros = [];

        $clienteId  = (int) ($_POST['cliente_id'] ?? 0);
        $desconto   = (float) str_replace(',', '.', $_POST['desconto'] ?? '0');
        $observacao = trim($_POST['observacao'] ?? '');

        // Itens vêm como JSON no campo hidden
        $itensJson = $_POST['itens_json'] ?? '[]';
        $itens     = json_decode($itensJson, true) ?? [];

        if (!$clienteId)       $erros['cliente_id'] = 'Selecione um cliente.';
        if ($desconto < 0 || $desconto > 100) $erros['desconto'] = 'Desconto deve ser entre 0 e 100%.';
        if (empty($itens))     $erros['itens'] = 'Adicione ao menos um item ao pedido.';

        // Valida cada item
        foreach ($itens as $i => $item) {
            if (empty($item['produto_id']))  $erros["item_{$i}"] = "Item " . ($i+1) . ": produto inválido.";
            if (empty($item['quantidade']) || $item['quantidade'] < 1) $erros["item_{$i}_qtd"] = "Item " . ($i+1) . ": quantidade inválida.";
        }

        // Recalcula subtotais no servidor (nunca confiar no JS para valores financeiros)
        foreach ($itens as &$item) {
            $item['quantidade'] = max(1, (int) $item['quantidade']);
            $item['preco_unit'] = round((float) $item['preco_unit'], 2);
            $item['subtotal']   = round($item['quantidade'] * $item['preco_unit'], 2);
        }

        return [$erros, compact('clienteId', 'desconto', 'observacao', 'itens')];
    }
}


