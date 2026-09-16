<?php
/**
 * MIDDLEWARE AUTH — garante que o usuário está autenticado.
 *
 * Se não houver sessão ativa:
 *   1. Salva o caminho atual em $_SESSION['redirect_after_login']
 *      para que o AuthController redirecione de volta após o login.
 *   2. Redireciona para /login.
 *
 * Executado pelo pipeline do index.php antes do controller.
 */
class Auth
{
    public static function handle(): void
    {
        if (!isset($_SESSION['user_id'])) {
            $_SESSION['redirect_after_login'] = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
            redirect('/login');
        }
    }
}


