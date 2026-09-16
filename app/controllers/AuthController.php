<?php

class AuthController extends BaseController
{
    public function __construct(
        private UserRepository $users = new UserRepository()
    ) {}

    public function login(): void
    {
        if (isLoggedIn()) $this->redirect('/dashboard');
        $this->view('auth/login');
    }

    public function doLogin(): void
    {
        $this->csrf();

        $errors = $this->validate([
            'email'    => ['label' => 'E-mail', 'email' => true],
            'password' => 'Senha',
        ]);

        if ($errors) {
            $this->view('auth/login', ['errors' => $errors]);
            return;
        }

        $email = post('email');
        $user  = $this->users->findByEmail($email);

        if (!$user || !password_verify(post('password'), $user['password'])) {
            appLog('warning', 'Login falhou: credenciais inválidas', __FILE__, __LINE__, [
                'email' => $email,
            ]);
            $this->view('auth/login', ['errors' => ['email' => 'E-mail ou senha incorretos.']]);
            return;
        }

        session_regenerate_id(true);
        RbacService::invalidate();

        $_SESSION['user_id']     = $user['id'];
        $_SESSION['user_name']   = $user['name'];
        $_SESSION['user_role']   = $user['role'];
        $_SESSION['user_email']  = $user['email'];

        appLog('info', 'Login realizado', __FILE__, __LINE__, [
            'email' => $email,
            'role'  => $user['role'],
        ]);

        $destination = $_SESSION['redirect_after_login'] ?? '/dashboard';
        unset($_SESSION['redirect_after_login']);
        $this->redirect($destination);
    }

    public function logout(): void
    {
        appLog('info', 'Logout', __FILE__, __LINE__);
        RbacService::invalidate();
        session_destroy();
        $this->redirect('/login');
    }
}


