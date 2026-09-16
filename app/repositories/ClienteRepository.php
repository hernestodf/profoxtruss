<?php

class ClienteRepository
{
    private PDO $db;

    public function __construct(?PDO $db = null)
    {
        $this->db = $db ?? getPDO();
    }

    public function all(): array
    {
        return $this->db
            ->query('SELECT * FROM clientes ORDER BY nome')
            ->fetchAll();
    }

    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM clientes WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public function findByCpf(string $cpf): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM clientes WHERE cpf = ? LIMIT 1');
        $stmt->execute([$cpf]);
        return $stmt->fetch() ?: null;
    }

    public function create(string $nome, string $cpf): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO clientes (nome, cpf) VALUES (?, ?)'
        );
        $stmt->execute([$nome, $cpf]);
        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, string $nome, string $cpf): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE clientes SET nome = ?, cpf = ? WHERE id = ?'
        );
        return $stmt->execute([$nome, $cpf, $id]);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare('DELETE FROM clientes WHERE id = ?');
        return $stmt->execute([$id]);
    }

    /**
     * Busca para autocomplete — pesquisa por nome OU CPF.
     */
    public function search(string $termo): array
    {
        $like = '%' . $termo . '%';
        $stmt = $this->db->prepare('
            SELECT id, nome, cpf
            FROM   clientes
            WHERE  nome LIKE ? OR cpf LIKE ?
            ORDER  BY nome
            LIMIT  10
        ');
        $stmt->execute([$like, $like]);
        return $stmt->fetchAll();
    }
}



