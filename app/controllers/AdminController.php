<?php

class AdminController extends BaseController
{
    public function __construct(
        private UserRepository       $users = new UserRepository(),
        private PermissionRepository $perms = new PermissionRepository(),
    ) {}

    public function index(): void
    {
        $this->authorize('admin.view');
        $this->view('admin/index', [
            'title'    => 'Painel Admin',
            'userName' => $_SESSION['user_name'],
        ]);
    }

    public function users(): void
    {
        $this->authorize('user.view');
        $this->view('admin/users', [
            'title'   => 'Usuários',
            'users'   => $this->users->all(),
            'columns' => [
                'id'         => 'ID',
                'name'       => 'Nome',
                'email'      => 'E-mail',
                'role'       => 'Cargo',
                'created_at' => 'Cadastro',
            ],
        ]);
    }

    public function showUser(): void
    {
        $this->authorize('user.view');
        $user = $this->users->find((int) $this->param('id'));
        if (!$user) $this->notFound();
        $this->view('admin/show-user', ['title' => 'Usuário', 'user' => $user]);
    }

    public function editUser(): void
    {
        $this->authorize('user.edit');
        $user = $this->users->find((int) $this->param('id'));
        if (!$user) $this->notFound();
        $this->view('admin/edit-user', [
            'title' => 'Editar Usuário',
            'user'  => $user,
            'roles' => RbacService::allRoles(),
        ]);
    }

    public function updateUser(): void
    {
        $this->authorize('user.edit');
        $this->csrf();

        $errors = $this->validate([
            'name'  => ['label' => 'Nome',   'min' => 2, 'max' => 100],
            'email' => ['label' => 'E-mail', 'email' => true],
        ]);

        $id = (int) $this->param('id');

        if ($errors) {
            $user = $this->users->find($id);
            $this->view('admin/edit-user', [
                'title'  => 'Editar Usuário',
                'user'   => $user,
                'roles'  => RbacService::allRoles(),
                'errors' => $errors,
            ]);
            return;
        }

        $this->users->update($id, ['name' => post('name'), 'email' => post('email')]);
        appLog('info', "user_id={$_SESSION['user_id']} atualizou user/{$id}");
        $this->redirect('/admin/users');
    }

    public function deleteUser(): void
    {
        $this->authorize('user.delete');
        $this->csrf();
        $id = (int) $this->param('id');
        $this->users->delete($id);
        appLog('info', "user_id={$_SESSION['user_id']} deletou user/{$id}");
        $this->redirect('/admin/users');
    }

    public function reports(): void
    {
        $this->authorize('report.view');
        $this->view('admin/reports', [
            'title' => 'Relatórios',
            'today' => DateUtil::toBr(DateUtil::today()),
        ]);
    }

    // ── Gerenciamento de permissões ───────────────────────────────────────────

    public function permissions(): void
    {
        $this->authorize('admin.view');
        $this->view('admin/permissions', [
            'title'       => 'Permissões',
            'roles'       => RbacService::allRoles(),
            'permissions' => RbacService::allPermissions(),
        ]);
    }

    public function grantPermission(): void
    {
        $this->authorize('admin.view');
        $this->csrf();
        RbacService::grant(post('role'), post('action'));
        $this->redirect('/admin/permissions');
    }

    public function revokePermission(): void
    {
        $this->authorize('admin.view');
        $this->csrf();
        RbacService::revoke(post('role'), post('action'));
        $this->redirect('/admin/permissions');
    }
}


