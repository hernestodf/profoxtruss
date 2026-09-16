<?php
/**
 * ENV — leitor de arquivo .env sem dependência externa.
 *
 * Por que usar .env?
 *   Credenciais de banco, chaves de API e configurações por ambiente
 *   não devem ficar hardcoded no código nem versionadas no git.
 *   O .env fica na raiz do projeto, no .gitignore, e cada ambiente
 *   (dev, staging, produção) tem o seu próprio.
 *
 * Formato suportado:
 *   DB_HOST=localhost
 *   DB_PASS="minha senha com espaço"
 *   APP_DEBUG=true
 *   # linhas que começam com # são comentários
 *
 * Comportamento:
 *   - Se o .env não existir, não é erro — usa as variáveis de ambiente do servidor
 *   - Não sobrescreve variáveis já definidas no ambiente (Docker, CI, hospedagem)
 *   - Idempotente: chamadas repetidas são ignoradas (static $loaded)
 *
 * Uso:
 *   Env::load(__DIR__ . '/.env');
 *   $host = Env::get('DB_HOST', 'localhost');
 *   $debug = Env::bool('APP_DEBUG', false);
 */
class Env
{
    private static bool $loaded = false;

    /** Lê o arquivo .env e popula $_ENV / putenv. */
    public static function load(string $path): void
    {
        if (self::$loaded) return;

        if (!file_exists($path)) return; // sem .env não é erro

        foreach (file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
            $line = trim($line);
            if (str_starts_with($line, '#') || !str_contains($line, '=')) continue;

            [$key, $value] = explode('=', $line, 2);
            $key   = trim($key);
            $value = trim($value);

            // Remove aspas simples ou duplas opcionais
            if (preg_match('/^(["\']).*\1$/', $value)) {
                $value = substr($value, 1, -1);
            }

            // Não sobrescreve variáveis já definidas no ambiente do servidor
            if (!array_key_exists($key, $_ENV) && getenv($key) === false) {
                $_ENV[$key] = $value;
                putenv("{$key}={$value}");
            }
        }

        self::$loaded = true;
    }

    /** Lê uma variável com valor padrão. */
    public static function get(string $key, string $default = ''): string
    {
        return $_ENV[$key] ?? (getenv($key) ?: $default);
    }

    /**
     * Lê uma variável e converte para bool.
     * Considera true: 'true', '1', 'yes', 'on' (case-insensitive).
     */
    public static function bool(string $key, bool $default = false): bool
    {
        $val = strtolower(self::get($key));
        if ($val === '') return $default;
        return in_array($val, ['true', '1', 'yes', 'on'], true);
    }
}


