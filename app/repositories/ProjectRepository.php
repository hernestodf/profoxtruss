<?php

class ProjectRepository
{
    private PDO $db;

    public function __construct(?PDO $db = null)
    {
        $this->db = $db ?? getPDO();
    }

    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM projects WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public function all(): array
    {
        return $this->db
            ->query('SELECT * FROM projects ORDER BY created_at DESC')
            ->fetchAll();
    }

    public function create(array $data): int
    {
        $stmt = $this->db->prepare('
            INSERT INTO projects (name, width, height, length, scale, components)
            VALUES (:name, :width, :height, :length, :scale, :components)
        ');
        $stmt->execute([
            ':name'       => $data['name'],
            ':width'      => $data['width'] ?? 0.00,
            ':height'     => $data['height'] ?? 0.00,
            ':length'     => $data['length'] ?? 0.00,
            ':scale'      => $data['scale'] ?? 50,
            ':components' => $data['components']
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        $stmt = $this->db->prepare('
            UPDATE projects
            SET name = :name,
                width = :width,
                height = :height,
                length = :length,
                scale = :scale,
                components = :components
            WHERE id = :id
        ');
        return $stmt->execute([
            ':id'         => $id,
            ':name'       => $data['name'],
            ':width'      => $data['width'],
            ':height'     => $data['height'],
            ':length'     => $data['length'],
            ':scale'      => $data['scale'],
            ':components' => $data['components']
        ]);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare('DELETE FROM projects WHERE id = ?');
        return $stmt->execute([$id]);
    }
}
