<?php
/**
 * LAYOUT — motor de template com herança simples.
 *
 * Problema que resolve: sem isso, cada view repetiria o <html>, <head>,
 * navbar e footer inteiros. Com Layout, cada view declara apenas seu
 * conteúdo e qual layout quer usar.
 *
 * Como funciona o ciclo de renderização:
 *   1. view('admin/users', $data)  →  chama Layout::render()
 *   2. Layout::render() inclui a view (ex: views/admin/users.php)
 *   3. A view chama Layout::set('main') e preenche blocos com Layout::start/end
 *   4. Após a view terminar, Layout::render() inclui o layout escolhido
 *   5. O layout chama Layout::slot('content') nos pontos certos para inserir os blocos
 *
 * Exemplo de view filha (views/admin/users.php):
 *
 *   <?php
 *   Layout::set('main');
 *   Layout::block('title', 'Usuários');
 *   Layout::start('content');
 *   ?>
 *   <h1>Lista de usuários</h1>
 *   <?php Layout::end(); ?>
 *
 * Exemplo de layout (views/layouts/main.php):
 *
 *   <title><?php Layout::slot('title', 'Sistema') ?></title>
 *   <body>
 *     <?php Layout::slot('content') ?>
 *   </body>
 *
 * Blocos disponíveis por convenção:
 *   'title'   → texto do <title>
 *   'head'    → CSS ou meta extras no <head>
 *   'content' → corpo principal da página
 *   'scripts' → JS extra antes do </body>
 */
class Layout
{
    private static ?string $current = null; // nome do layout escolhido pela view
    private static array   $blocks  = [];   // conteúdo capturado por bloco

    /** Define qual layout a view vai usar. Deve ser chamado no topo da view. */
    public static function set(string $name): void
    {
        self::$current = $name;
    }

    /**
     * Define o conteúdo de um bloco com string ou closure.
     *
     * Layout::block('title', 'Usuários');
     * Layout::block('content', function() { echo '<p>Olá</p>'; });
     */
    public static function block(string $name, string|callable $content): void
    {
        if (is_callable($content)) {
            ob_start();
            $content();
            self::$blocks[$name] = ob_get_clean();
        } else {
            self::$blocks[$name] = $content;
        }
    }

    /**
     * Inicia a captura de um bloco com HTML.
     * Fechar com Layout::end().
     *
     * Layout::start('content');
     *   echo '<h1>Conteúdo</h1>';
     * Layout::end();
     */
    public static function start(string $name): void
    {
        self::$blocks['__capturing'] = $name;
        ob_start();
    }

    /** Encerra a captura iniciada com start() e salva o bloco. */
    public static function end(): void
    {
        $name = self::$blocks['__capturing'] ?? null;
        if ($name) {
            self::$blocks[$name] = ob_get_clean();
            unset(self::$blocks['__capturing']);
        }
    }

    /**
     * Exibe o conteúdo de um bloco dentro do layout.
     * Usado pelo arquivo de layout para inserir os blocos da view filha.
     *
     * Layout::slot('content')           → exibe o bloco ou string vazia
     * Layout::slot('title', 'Sistema')  → exibe o bloco ou o fallback
     */
    public static function slot(string $name, string $default = ''): void
    {
        echo self::$blocks[$name] ?? $default;
    }

    /**
     * Ponto de entrada chamado por view() no helpers.php.
     * Carrega a view filha (que preenche os blocos) e depois o layout.
     */
    public static function render(string $viewPath, array $data = []): void
    {
        self::$current = null;
        self::$blocks  = [];

        // Extrai os dados como variáveis locais e carrega a view filha
        extract($data, EXTR_SKIP);
        require $viewPath;

        // Se a view não chamou Layout::set(), ela se renderizou sozinha — encerra
        if (!self::$current) {
            return;
        }

        // Carrega o layout que a view escolheu
        $layoutPath = ROOT . '/app/views/layouts/' . self::$current . '.php';

        if (!file_exists($layoutPath)) {
            error_log("Layout não encontrado: {$layoutPath}");
            http_response_code(500);
            die('Erro interno: layout não encontrado.');
        }

        extract($data, EXTR_SKIP);
        require $layoutPath;
    }
}


