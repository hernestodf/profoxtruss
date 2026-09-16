<?php
/**
 * CONFIG — carregado antes de tudo pelo index.php.
 *
 * Responsabilidades:
 *   - Carrega o .env (Env::load) — sem ele, usa variáveis de ambiente do servidor
 *   - Inicia a sessão com configurações seguras
 *   - Configura nível de log e exibição de erros por ambiente
 *   - Define constantes globais (APP_ENV, APP_DEBUG)
 *   - Expõe getPDO() — conexão lazy, singleton, reutilizada no request inteiro
 *
 * IMPORTANTE: BASE_PATH é definido no index.php (depende de $_SERVER),
 * não aqui. BASE_URL hardcoded não existe mais — use url() ou redirect().
 */

// ─── Env ──────────────────────────────────────────────────────────────────────
// Carregado manualmente pois o autoload ainda não foi registrado.
// Env::load() é idempotente — chamadas repetidas são ignoradas.
require_once __DIR__ . '/app/utils/Env.php';
Env::load(__DIR__ . '/.env');

// ─── Sessão ───────────────────────────────────────────────────────────────────
ini_set('session.cookie_httponly', 1);   // JS não acessa o cookie de sessão
ini_set('session.use_strict_mode', 1);   // rejeita session IDs externos
ini_set('session.gc_maxlifetime', (int) Env::get('SESSION_LIFETIME', '7200'));
session_start();

// ─── Logs e erros ─────────────────────────────────────────────────────────────
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/logs/app.log');

// Em desenvolvimento: mostra erros na tela. Em produção: só loga, nunca exibe.
$debug = Env::bool('APP_DEBUG', false);
ini_set('display_errors', $debug ? '1' : '0');
error_reporting($debug ? E_ALL : E_ERROR | E_WARNING);

// ─── Constantes ───────────────────────────────────────────────────────────────
define('APP_ENV',   Env::get('APP_ENV', 'production'));
define('APP_DEBUG', $debug);
// LOG_TRACE: inclui stack trace completo no log de exceções (só em dev)
define('LOG_TRACE', APP_ENV === 'development');

// ─── Banco de Dados ───────────────────────────────────────────────────────────
/**
 * Retorna a conexão PDO.
 * Singleton por request — a conexão é criada na primeira chamada e reutilizada.
 * Lança PDOException se as credenciais forem inválidas (capturado pelo ErrorHandler).
 *
 * Uso: $pdo = getPDO();
 * Nos Repositories: $this->db = getPDO();
 */
function getPDO(): PDO
{
    static $pdo = null;

    if ($pdo === null) {
        $dbPathFromEnv = Env::get('DB_PATH', 'database/database.sqlite');
        
        // If DB_PATH is relative, resolve it relative to the root directory where config.php lies
        if (substr($dbPathFromEnv, 0, 1) !== '/' && !preg_match('/^[a-zA-Z]:\\\\/', $dbPathFromEnv)) {
            $dbPath = __DIR__ . '/' . $dbPathFromEnv;
        } else {
            $dbPath = $dbPathFromEnv;
        }

        // Ensure parent directory exists
        $dbDir = dirname($dbPath);
        if (!is_dir($dbDir)) {
            mkdir($dbDir, 0777, true);
        }

        $pdo = new PDO(
            'sqlite:' . $dbPath,
            null,
            null,
            [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,   // lança exceção em erro SQL
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,         // arrays associativos por padrão
            ]
        );
        
        // Enable foreign key constraints in SQLite
        $pdo->exec('PRAGMA foreign_keys = ON;');
    }

    return $pdo;
}


