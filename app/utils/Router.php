<?php
/**
 * ROUTER — registro e resolução de rotas HTTP.
 *
 * Design: estático, sem instância. As rotas são registradas durante o boot
 * (require routes.php) e ficam em memória até o fim do request.
 *
 * Funcionalidades:
 *   - Verbos HTTP explícitos: get, post, put, delete, any
 *   - Parâmetros dinâmicos:   /user/{id}, /post/{slug}/edit
 *   - Grupos com prefixo e middlewares herdados (aninhamento ilimitado)
 *
 * Como usar nos arquivos de rota (routes/*.php):
 *
 *   Router::get('/users', ['UserController', 'index']);
 *   Router::post('/users', ['UserController', 'store']);
 *
 *   Router::group('/admin', ['Auth'], function () {
 *       Router::get('', ['AdminController', 'index']);
 *
 *       Router::group('/users', ['Rbac:user.view'], function () {
 *           Router::get('',           ['AdminController', 'users']);
 *           Router::get('/{id}',      ['AdminController', 'showUser']);
 *           Router::post('/{id}',     ['AdminController', 'updateUser']);
 *           Router::delete('/{id}',   ['AdminController', 'deleteUser']);
 *       });
 *   });
 *
 * Como usar no index.php:
 *   $match = Router::match('GET', '/admin/users/42');
 *   // retorna: ['AdminController', 'showUser', ['Auth','Rbac:user.view'], ['id'=>'42']]
 *   // ou null se nenhuma rota casar
 */
class Router
{
    // Lista acumulada de rotas registradas neste request
    private static array $routes = [];

    // Pilha de contextos de grupo ativos (permite grupos aninhados)
    private static array $groupStack = [];

    // ── Métodos de registro ───────────────────────────────────────────────────

    public static function get(string $uri, array $handler, array $middlewares = []): void
    {
        self::add('GET', $uri, $handler, $middlewares);
    }

    public static function post(string $uri, array $handler, array $middlewares = []): void
    {
        self::add('POST', $uri, $handler, $middlewares);
    }

    public static function put(string $uri, array $handler, array $middlewares = []): void
    {
        self::add('PUT', $uri, $handler, $middlewares);
    }

    public static function delete(string $uri, array $handler, array $middlewares = []): void
    {
        self::add('DELETE', $uri, $handler, $middlewares);
    }

    /** Aceita qualquer verbo HTTP — útil para rotas de debug ou webhooks */
    public static function any(string $uri, array $handler, array $middlewares = []): void
    {
        self::add('ANY', $uri, $handler, $middlewares);
    }

    // ── Grupos ────────────────────────────────────────────────────────────────

    /**
     * Agrupa rotas com prefixo de URI e middlewares compartilhados.
     * Grupos podem ser aninhados — o prefixo e os middlewares se acumulam.
     *
     * Router::group('/admin', ['Auth'], function () {
     *     Router::group('/users', ['Rbac:user.view'], function () {
     *         Router::get('/{id}', ['AdminController', 'show']);
     *         // URI final: /admin/users/{id}
     *         // Middlewares: ['Auth', 'Rbac:user.view']
     *     });
     * });
     */
    public static function group(string $prefix, array $middlewares, callable $callback): void
    {
        self::$groupStack[] = [
            'prefix'      => self::currentPrefix() . $prefix,
            'middlewares' => array_merge(self::currentMiddlewares(), $middlewares),
        ];

        $callback();

        array_pop(self::$groupStack);
    }

    // ── Resolução ─────────────────────────────────────────────────────────────

    /**
     * Tenta casar o método HTTP e o caminho com uma rota registrada.
     *
     * Retorna array com 4 posições ou null se não encontrar:
     *   [0] string   — nome da classe do controller
     *   [1] string   — nome do método
     *   [2] string[] — lista de middlewares a executar
     *   [3] array    — parâmetros dinâmicos extraídos da URI (['id' => '42'])
     */
    public static function match(string $httpMethod, string $requestPath): ?array
    {
        $path = rtrim($requestPath, '/') ?: '/';

        foreach (self::$routes as $route) {
            // Verifica verbo HTTP (ANY casa com qualquer um)
            if ($route['method'] !== 'ANY' && $route['method'] !== strtoupper($httpMethod)) {
                continue;
            }

            // Match exato — sem regex, mais rápido
            if ($route['uri'] === $path) {
                return [...$route['handler'], $route['middlewares'], []];
            }

            // Match com parâmetros dinâmicos (ex: /user/{id})
            if (!str_contains($route['uri'], '{')) {
                continue;
            }

            $regex = self::toRegex($route['uri'], $paramNames);

            if (preg_match($regex, $path, $matches)) {
                array_shift($matches); // remove o match completo, fica só os grupos
                return [...$route['handler'], $route['middlewares'], array_combine($paramNames, $matches)];
            }
        }

        return null;
    }

    /** Retorna todas as rotas registradas — útil para debug ou documentação. */
    public static function all(): array
    {
        return self::$routes;
    }

    // ── Internos ──────────────────────────────────────────────────────────────

    private static function add(string $method, string $uri, array $handler, array $middlewares): void
    {
        $fullUri = self::currentPrefix() . $uri;
        $fullUri = ($fullUri === '' || $fullUri === '/') ? '/' : $fullUri;

        self::$routes[] = [
            'method'      => $method,
            'uri'         => $fullUri,
            'handler'     => $handler,                                          // [ControllerClass, method]
            'middlewares' => array_merge(self::currentMiddlewares(), $middlewares),
        ];
    }

    private static function currentPrefix(): string
    {
        return end(self::$groupStack)['prefix'] ?? '';
    }

    private static function currentMiddlewares(): array
    {
        return end(self::$groupStack)['middlewares'] ?? [];
    }

    /**
     * Converte '/user/{id}/edit' em regex '#^/user/([^/]+)/edit$#'
     * e preenche $paramNames com ['id'].
     */
    private static function toRegex(string $pattern, ?array &$paramNames): string
    {
        $paramNames = [];

        $regex = preg_replace_callback('/\{(\w+)\}/', function ($m) use (&$paramNames) {
            $paramNames[] = $m[1];
            return '([^/]+)';
        }, $pattern);

        return '#^' . $regex . '$#';
    }
}


