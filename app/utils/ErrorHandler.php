<?php

class ErrorHandler
{
    public static function register(): void
    {
        set_exception_handler(function (Throwable $e) {
            self::logException($e);
            self::serverError();
        });

        register_shutdown_function(function () {
            $err = error_get_last();
            if ($err && in_array($err['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
                self::logFatal($err);
                self::serverError();
            }
        });

        set_error_handler(function (int $severity, string $message, string $file, int $line): bool {
            if (!(error_reporting() & $severity)) return false;
            throw new ErrorException($message, 0, $severity, $file, $line);
        });
    }

    // ── Respostas HTTP ────────────────────────────────────────────────────────

    public static function notFound(): never
    {
        http_response_code(404);
        self::render('errors/404', 'Página não encontrada');
        exit;
    }

    public static function forbidden(): never
    {
        http_response_code(403);
        self::render('errors/403', 'Acesso negado');
        exit;
    }

    public static function serverError(string $logMessage = ''): never
    {
        if ($logMessage) {
            appLog('error', $logMessage);
        }
        http_response_code(500);
        self::render('errors/500', 'Erro interno');
        exit;
    }

    // ── Log estruturado ───────────────────────────────────────────────────────

    private static function logException(Throwable $e): void
    {
        $context = [
            'class' => get_class($e),
            'file'  => basename($e->getFile()) . ':' . $e->getLine(),
            'ip'    => $_SERVER['REMOTE_ADDR'] ?? 'cli',
            'uri'   => $_SERVER['REQUEST_URI'] ?? '',
        ];

        if (isset($_SESSION['user_id'])) {
            $context['user'] = $_SESSION['user_id'];
        }

        $ctx  = implode(' | ', array_map(fn($k, $v) => "{$k}:{$v}", array_keys($context), $context));
        $line = sprintf('[%s] [EXCEPTION] %s | %s', date('Y-m-d H:i:s'), $e->getMessage(), $ctx);

        if (defined('LOG_TRACE') && LOG_TRACE) {
            // Trace resumido: só as 5 primeiras linhas, sem path absoluto
            $trace = array_slice(explode("\n", $e->getTraceAsString()), 0, 5);
            $trace = array_map(fn($l) => preg_replace('#' . preg_quote(ROOT, '#') . '#', '', $l), $trace);
            $line .= "\n  Trace: " . implode("\n         ", $trace);
        }

        error_log($line);
    }

    private static function logFatal(array $err): void
    {
        $context = [
            'file' => basename($err['file']) . ':' . $err['line'],
            'ip'   => $_SERVER['REMOTE_ADDR'] ?? 'cli',
        ];
        $ctx  = implode(' | ', array_map(fn($k, $v) => "{$k}:{$v}", array_keys($context), $context));
        error_log(sprintf('[%s] [FATAL] %s | %s', date('Y-m-d H:i:s'), $err['message'], $ctx));
    }

    // ── Render ────────────────────────────────────────────────────────────────

    private static function render(string $template, string $fallbackTitle): void
    {
        $path = ROOT . '/app/views/' . $template . '.php';

        if (file_exists($path)) {
            Layout::render($path, ['title' => $fallbackTitle]);
        } else {
            echo "<!DOCTYPE html><html><head><title>{$fallbackTitle}</title></head>"
               . "<body style='font-family:sans-serif;padding:2rem'>"
               . "<h1>{$fallbackTitle}</h1><a href='" . url('/') . "'>Voltar ao início</a></body></html>";
        }
    }
}


