<?php

class PermissionRepository
{
    private PDO $db;

    public function __construct(?PDO $db = null)
    {
        $this->db = $db ?? getPDO();
    }

    /**
     * Retorna array de actions para um cargo.
     * Ex: ['dashboard.view', 'user.edit', 'report.view']
     */
    public function forRole(string $role): array
    {
        $stmt = $this->db->prepare('
            SELECT p.action
            FROM permissions p
            JOIN role_permissions rp ON rp.permission_id = p.id
            JOIN roles r             ON r.id = rp.role_id
            WHERE r.name = ?
        ');
        $stmt->execute([$role]);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    /**
     * Retorna todos os cargos cadastrados.
     */
    public function allRoles(): array
    {
        return $this->db->query('SELECT name FROM roles ORDER BY name')
                        ->fetchAll(PDO::FETCH_COLUMN);
    }

    /**
     * Concede uma permissão a um cargo.
     * Cria a permissão se não existir.
     */
    public function grant(string $role, string $action): void
    {
        $this->ensurePermission($action);

        $this->db->prepare('
            INSERT IGNORE INTO role_permissions (role_id, permission_id)
            SELECT r.id, p.id FROM roles r, permissions p
            WHERE r.name = ? AND p.action = ?
        ')->execute([$role, $action]);
    }

    /**
     * Revoga uma permissão de um cargo.
     */
    public function revoke(string $role, string $action): void
    {
        $this->db->prepare('
            DELETE rp FROM role_permissions rp
            JOIN roles r       ON r.id = rp.role_id
            JOIN permissions p ON p.id = rp.permission_id
            WHERE r.name = ? AND p.action = ?
        ')->execute([$role, $action]);
    }

    /**
     * Lista todas as permissões disponíveis no sistema.
     */
    public function allPermissions(): array
    {
        return $this->db->query('SELECT action FROM permissions ORDER BY action')
                        ->fetchAll(PDO::FETCH_COLUMN);
    }

    /**
     * Garante que a permissão existe na tabela permissions.
     */
    private function ensurePermission(string $action): void
    {
        $this->db->prepare('INSERT IGNORE INTO permissions (action) VALUES (?)')
                 ->execute([$action]);
    }
}


