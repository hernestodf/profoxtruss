<?php

/**
 * Controller base com injeção de dependência manual no construtor.
 *
 * Padrão para controllers filhos:
 *
 *   class UserController extends BaseController
 *   {
 *       public function __construct(
 *           private UserRepository    $users    = new UserRepository(),
 *           private PermissionRepository $perms = new PermissionRepository(),
 *       ) {}
 *   }
 *
 * Benefício: quando UserRepository precisar de um novo colaborador,
 * você muda o construtor de UserRepository — não todos os controllers.
 * Em testes, passa um mock no lugar da dependência real.
 */
abstract class BaseController
{
    // ── View ──────────────────────────────────────────────────────────────────

    protected function view(string $template, array $data = []): void
    {
        view($template, $data);
    }

    // ── Resposta ──────────────────────────────────────────────────────────────

    protected function json(mixed $data, int $status = 200): never
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    protected function redirect(string $url): never
    {
        redirect($url);
    }

    // ── Segurança ─────────────────────────────────────────────────────────────

    protected function authorize(string $action): void
    {
        RbacService::authorize($action);
    }

    protected function csrf(): void
    {
        csrfValidate();
    }

    // ── Rota ──────────────────────────────────────────────────────────────────

    protected function param(string $name, mixed $default = null): mixed
    {
        return $GLOBALS['_ROUTE_PARAMS'][$name] ?? $default;
    }

    // ── Validação ─────────────────────────────────────────────────────────────

    /**
     * Valida campos do POST.
     *
     * Forma curta  — só obrigatório:
     *   'campo' => 'Label'
     *
     * Forma completa — com regras:
     *   'campo' => ['label' => 'Label', 'email' => true, 'min' => 3, 'max' => 100]
     *
     * Retorna array de erros (vazio = tudo válido).
     */
    protected function validate(array $rules): array
    {
        $errors = [];

        foreach ($rules as $field => $rule) {
            $value = trim($_POST[$field] ?? '');

            if (is_string($rule)) {
                if ($value === '') $errors[$field] = "O campo {$rule} é obrigatório.";
                continue;
            }

            $label    = $rule['label'] ?? $field;
            $required = $rule['required'] ?? true;

            if ($required && $value === '') {
                $errors[$field] = "O campo {$label} é obrigatório.";
                continue;
            }

            if (!empty($rule['email']) && $value && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                $errors[$field] = "O campo {$label} deve ser um e-mail válido.";
            }

            if (isset($rule['min']) && mb_strlen($value) < $rule['min']) {
                $errors[$field] = "O campo {$label} deve ter ao menos {$rule['min']} caracteres.";
            }

            if (isset($rule['max']) && mb_strlen($value) > $rule['max']) {
                $errors[$field] = "O campo {$label} deve ter no máximo {$rule['max']} caracteres.";
            }
        }

        return $errors;
    }

    // ── Erros HTTP ────────────────────────────────────────────────────────────

    protected function notFound(): never
    {
        ErrorHandler::notFound();
    }

    protected function forbidden(): never
    {
        ErrorHandler::forbidden();
    }
}


