<?php

namespace Kantodo\Core;

/**
 * HTML header
 */
class HeaderHTML
{
    /**
     * @var array
     */
    private $styles = [];
    /**
     * @var array
     */
    private $scripts = [];

    /**
     * @var string
     */
    private $title = 'Kantodo';

    /**
     * Registruje styl
     *
     * @param   string  $url       url
     * @param   bool    $external  externí
     * @param   string  $media     media atribut
     *
     * @return  void
     */
    public function registerStyle(string $url, bool $external = false, string $media = 'all')
    {
        if (!$external) {
            $url = Application::$URL_PATH . $url;
        }

        $this->styles[] = ['url' => $url, 'media' => $media];
    }

    /**
     * Registruje skript
     *
     * @param   string  $url       url
     * @param   bool    $external  externí
     * @param   bool    $defer     defer
     * @param   bool    $async     async
     * @param   string  $type      typ (module,text/javascript)
     *
     * @return  void
     */
    public function registerScript(string $url, bool $external = false, bool $defer = false, bool $async = false, string $type = '')
    {
        if (!$external) {
            $url = Application::$URL_PATH . $url;
        }

        $this->scripts[] = [
            'url'   => $url,
            'defer' => $defer,
            'async' => $async,
            'type'  => $type,
        ];
    }

    /**
     * Nastaví title
     *
     * @param   string  $title  title
     *
     * @return  void
     */
    public function setTitle(string $title)
    {
        $this->title = $title;
    }

    /**
     * Získá styly
     *
     * @return  array
     */
    public function getStyles()
    {
        return $this->styles;
    }

    /**
     * Získá skripty
     *
     * @return  array
     */
    public function getScripts()
    {
        return $this->scripts;
    }

    /**
     * Vrátí vnitřek html head
     *
     * @return  string
     */
    public function getContent()
    {
        $header = '';

        foreach ($this->styles as $style) {
            $header .= "<link rel='stylesheet' href='{$style['url']}' media='{$style['media']}'>";
        }

        $header .= "<title>{$this->title}</title>";

        foreach ($this->scripts as $script) {
            $attr = ($script['defer']) ? 'defer ' : '';
            $attr .= ($script['async']) ? 'async' : '';

            $type = ($script['type'] != '') ? "type='{$script['type']}'" : '';

            $header .= "<script {$type}' src='{$script['url']}' {$attr}></script>";
        }

        return $header;
    }
}
