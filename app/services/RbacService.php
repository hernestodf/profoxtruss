<?php

/**
 * RBAC baseado em banco de dados com cache de sessão.
 *
 * Fluxo:
 *   1. Login → sessão criada com user_role
 *   2. Primeira requisição protegida → carrega permissões do banco, salva na sessão
 *   3. Requisições seguintes → lê da sessão (zero queries)
 *   4. Logout / troca de cargo → invalidate() limpa o cache
 *
 * Para mudar permissões sem deploy:
 *   RbacService::grant('editor', 'report.export');
 *   RbacService::revoke('editor', 'report.export');
 */
class RbacService
{
    private const SESSION_KEY  = '_rbac_permissions';
    private const SESSION_ROLE = '_rbac_role';

    // ── Verificação ───────────────────────────────────────────────────────────

    /**
     * Verifica se o usuário logado pode executar uma ação.
     */
    public static function can(string $action): bool
    {
        $role = $_SESSION['user_role'] ?? '';
        if (!$role) return false;
        return in_array($action, self::loadForRole($role), true);
    }

    /**
     * Encerra com 403 se o usuário não puder executar a ação.
     */
    public static function authorize(string $action): void
    {
        if (!self::can($action)) {
            appLog('warning', 'Acesso negado: permissão insuficiente', '', 0, [
                'action' => $action,
                'role'   => $_SESSION['user_role'] ?? '?',
                'uri'    => $_SERVER['REQUEST_URI'] ?? '?',
            ]);
            ErrorHandler::forbidden();
        }
    }

    // ── Cache ─────────────────────────────────────────────────────────────────

    /**
     * Retorna as permissões do cargo — da sessão ou do banco.
     */
    public static function loadForRole(string $role): array
    {
        $cached     = $_SESSION[self::SESSION_KEY]  ?? null;
        $cachedRole = $_SESSION[self::SESSION_ROLE] ?? null;

        if ($cached !== null && $cachedRole === $role) {
            return $cached;
        }

        $permissions = (new PermissionRepository())->forRole($role);

        $_SESSION[self::SESSION_KEY]  = $permissions;
        $_SESSION[self::SESSION_ROLE] = $role;

        return $permissions;
    }

    /**
     * Limpa o cache. Chamar no logout e ao alterar o cargo de um usuário.
     */
    public static function invalidate(): void
    {
        unset($_SESSION[self::SESSION_KEY], $_SESSION[self::SESSION_ROLE]);
    }

    // ── Gerenciamento ─────────────────────────────────────────────────────────

    public static function grant(string $role, string $action): void
    {
        (new PermissionRepository())->grant($role, $action);
        self::invalidate();
    }

    public static function revoke(string $role, string $action): void
    {
        (new PermissionRepository())->revoke($role, $action);
        self::invalidate();
    }

    public static function allRoles(): array
    {
        return (new PermissionRepository())->allRoles();
    }

    public static function permissionsFor(string $role): array
    {
        return (new PermissionRepository())->forRole($role);
    }

    public static function allPermissions(): array
    {
        return (new PermissionRepository())->allPermissions();
    }
}


