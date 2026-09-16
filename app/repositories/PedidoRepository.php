<?php

class PedidoRepository
{
    private PDO $db;

    public function __construct(?PDO $db = null)
    {
        $this->db = $db ?? getPDO();
    }

    public function all(): array
    {
        return $this->db->query('
            SELECT p.*, c.nome AS cliente_nome
            FROM   pedidos p
            JOIN   clientes c ON c.id = p.cliente_id
            ORDER  BY p.created_at DESC
        ')->fetchAll();
    }

    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare('
            SELECT p.*, c.nome AS cliente_nome
            FROM   pedidos p
            JOIN   clientes c ON c.id = p.cliente_id
            WHERE  p.id = ?
            LIMIT  1
        ');
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public function itens(int $pedidoId): array
    {
        $stmt = $this->db->prepare('
            SELECT pi.*, pr.nome AS produto_nome, pr.codigo AS produto_codigo
            FROM   pedido_itens pi
            JOIN   produtos pr ON pr.id = pi.produto_id
            WHERE  pi.pedido_id = ?
            ORDER  BY pi.id
        ');
        $stmt->execute([$pedidoId]);
        return $stmt->fetchAll();
    }

    /**
     * Cria pedido + itens numa transação.
     * $itens = [['produto_id' => 1, 'quantidade' => 2, 'preco_unit' => 99.90], ...]
     */
    public function create(int $clienteId, float $desconto, string $observacao, array $itens): int
    {
        $this->db->beginTransaction();

        try {
            $total = $this->calcularTotal($itens, $desconto);

            $stmt = $this->db->prepare('
                INSERT INTO pedidos (cliente_id, desconto, total, observacao)
                VALUES (?, ?, ?, ?)
            ');
            $stmt->execute([$clienteId, $desconto, $total, $observacao]);
            $pedidoId = (int) $this->db->lastInsertId();

            $this->inserirItens($pedidoId, $itens);

            $this->db->commit();
            return $pedidoId;

        } catch (Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function update(int $id, int $clienteId, float $desconto, string $observacao, array $itens): void
    {
        $this->db->beginTransaction();

        try {
            $total = $this->calcularTotal($itens, $desconto);

            $this->db->prepare('
                UPDATE pedidos SET cliente_id=?, desconto=?, total=?, observacao=? WHERE id=?
            ')->execute([$clienteId, $desconto, $total, $observacao, $id]);

            // Remove itens antigos e reinsere — simples e correto
            $this->db->prepare('DELETE FROM pedido_itens WHERE pedido_id = ?')->execute([$id]);
            $this->inserirItens($id, $itens);

            $this->db->commit();

        } catch (Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function delete(int $id): bool
    {
        return $this->db->prepare('DELETE FROM pedidos WHERE id = ?')->execute([$id]);
    }

    // ── Internos ──────────────────────────────────────────────────────────────

    private function calcularTotal(array $itens, float $desconto): float
    {
        $subtotal = array_sum(array_column($itens, 'subtotal'));
        return round($subtotal * (1 - $desconto / 100), 2);
    }

    private function inserirItens(int $pedidoId, array $itens): void
    {
        $stmt = $this->db->prepare('
            INSERT INTO pedido_itens (pedido_id, produto_id, quantidade, preco_unit, subtotal)
            VALUES (?, ?, ?, ?, ?)
        ');
        foreach ($itens as $item) {
            $stmt->execute([
                $pedidoId,
                $item['produto_id'],
                $item['quantidade'],
                $item['preco_unit'],
                $item['subtotal'],
            ]);
        }
    }
}


