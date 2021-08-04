<?php 

namespace Kantodo\Core;

class HeaderHTML
{
    private $styles = array();
    private $scripts = array();
    private $title = 'Kantodo';

    public function registerStyle(string $url, bool $external = false, string $media = 'all') 
    {
        if (!$external)
            $url = Application::$URL_PATH . $url;

        $this->styles[] = ['url' => $url, 'media' => $media];
    }


    public function registerScript(string $url, bool $external = false, bool $defer = false, bool $async = false, string $type = '') 
    {
        if (!$external)
            $url = Application::$URL_PATH . $url;

        $this->scripts[] = [
            'url' => $url,
            'defer' => $defer,
            'async' => $async,
            'type' => $type
        ];
    }

    public function setTitle(string $title)
    {
        $this->title = $title;
    }

    public function getStyles() 
    {
        return $this->styles;
    }

    public function getScripts() 
    {
        return $this->scripts;
    }

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



?>