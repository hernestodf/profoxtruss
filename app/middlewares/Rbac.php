<?php

/**
 * Middleware de verificação de permissão por ação.
 *
 * Uso nas rotas: 'Rbac:user.view', 'Rbac:report.export'
 * Delega toda a lógica ao RbacService — o middleware só faz a ponte.
 */
class Rbac
{
    public static function handle(string $action): void
    {
        RbacService::authorize($action);
    }
}


