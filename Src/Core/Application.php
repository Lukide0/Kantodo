<?php

declare(strict_types = 1);

namespace Kantodo\Core;

/**
 * Aplikace
 */
class Application extends BaseApplication
{

    /**
     * @var Application
     */
    public static $APP;

    /**
     * Cesta k migraci
     *
     * @var string
     */
    public static $MIGRATION_DIR;

    /**
     * Cesta ke skriptům
     *
     * @var string
     */
    public static $SCRIPT_URL;

    /**
     * Cesta ke Model, View a Controler
     *
     * @var string
     */
    public static $PAGES_DIR;

    /**
     * Cesta ke stylům
     *
     * @var string
     */
    public static $STYLE_URL;

    /**
     * @var bool
     */
    public static $INSTALLING = false;

    /**
     * @var HeaderHTML
     */
    public $header;

    /**
     * @var Router
     */
    public $router;

    public function __construct()
    {
        parent::__construct();

        self::$APP = $this;

        self::$MIGRATION_DIR = self::$ROOT_DIR . '/migrations';
        self::$SCRIPT_URL    = self::$URL_PATH . '/scripts/';
        self::$STYLE_URL     = self::$URL_PATH . '/styles/';
        self::$PAGES_DIR     = self::$ROOT_DIR . '/Pages';

        $this->header = new HeaderHTML();
        $this->router = new Router($this->request, $this->response);
    }

    /**
     * Přepíše config.php
     *
     * @param   array<string,string>  $constants  konstanty
     *
     * @return  void
     */
    public static function overrideConfig(array $constants)
    {
        $content = '';

        foreach ($constants as $key => $value) {
            $content .= "define('{$key}', {$value});\n";
        }

        $content = "<?php\n{$content}\n?>";

        file_put_contents(self::$ROOT_DIR . '/config.php', $content);
    }

    /**
     * Přidá do configu konstanty
     *
     * @param   array<string,string>  $constants
     *
     * @return  void
     */
    public static function addToConfig(array $constants)
    {
        $content = file_get_contents(self::$ROOT_DIR . '/config.php') . "\n\n";

        foreach ($constants as $key => $value) {
            $content .= "define('{$key}', {$value});\n";
        }

        file_put_contents(self::$ROOT_DIR . '/config.php', $content);
    }

    /**
     * Začne aplikaci
     *
     * @return  void
     */
    public function run()
    {
        $this->router->resolve();
    }
}
