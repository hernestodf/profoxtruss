<?php

class ProdutoRepository
{
    private PDO $db;

    public function __construct(?PDO $db = null)
    {
        $this->db = $db ?? getPDO();
    }

    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM produtos WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public function findByCodigo(string $codigo): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM produtos WHERE codigo = ? LIMIT 1');
        $stmt->execute([$codigo]);
        return $stmt->fetch() ?: null;
    }

    public function all(bool $onlyActive = true): array
    {
        $sql = 'SELECT * FROM produtos';
        if ($onlyActive) {
            $sql .= ' WHERE ativo = 1';
        }
        $sql .= ' ORDER BY tipo, comprimento, nome';
        return $this->db->query($sql)->fetchAll();
    }

    /**
     * Busca para autocomplete — retorna id, codigo, nome, preco, estoque, tipo, comprimento, peso.
     * Pesquisa por nome OU código. Máximo 10 resultados.
     */
    public function search(string $termo): array
    {
        $like = '%' . $termo . '%';
        $stmt = $this->db->prepare('
            SELECT id, codigo, nome, tipo, comprimento, peso, preco, estoque
            FROM   produtos
            WHERE  ativo = 1
            AND    (nome LIKE ? OR codigo LIKE ?)
            ORDER  BY nome
            LIMIT  10
        ');
        $stmt->execute([$like, $like]);
        return $stmt->fetchAll();
    }

    public function create(array $data): int
    {
        $stmt = $this->db->prepare('
            INSERT INTO produtos (codigo, nome, tipo, comprimento, peso, preco, estoque, ativo, cor)
            VALUES (:codigo, :nome, :tipo, :comprimento, :peso, :preco, :estoque, :ativo, :cor)
        ');
        $stmt->execute([
            ':codigo'      => $data['codigo'],
            ':nome'        => $data['nome'],
            ':tipo'        => $data['tipo'],
            ':comprimento' => $data['comprimento'] ?? 0.00,
            ':peso'        => $data['peso'] ?? 0.00,
            ':preco'       => $data['preco'] ?? 0.00,
            ':estoque'     => $data['estoque'] ?? 0,
            ':ativo'       => $data['ativo'] ?? 1,
            ':cor'         => $data['cor'] ?? ''
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        $stmt = $this->db->prepare('
            UPDATE produtos
            SET codigo = :codigo,
                nome = :nome,
                tipo = :tipo,
                comprimento = :comprimento,
                peso = :peso,
                preco = :preco,
                estoque = :estoque,
                ativo = :ativo,
                cor = :cor
            WHERE id = :id
        ');
        return $stmt->execute([
            ':id'          => $id,
            ':codigo'      => $data['codigo'],
            ':nome'        => $data['nome'],
            ':tipo'        => $data['tipo'],
            ':comprimento' => $data['comprimento'],
            ':peso'        => $data['peso'],
            ':preco'       => $data['preco'],
            ':estoque'     => $data['estoque'],
            ':ativo'       => $data['ativo'],
            ':cor'         => $data['cor'] ?? ''
        ]);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare('DELETE FROM produtos WHERE id = ?');
        return $stmt->execute([$id]);
    }
}


