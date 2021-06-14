<?php 

namespace Kantodo\Core;

class HeaderHTML
{
    private $styles = array();
    private $scripts = array();
    private $title = 'Kantodo';

    public function RegisterStyle(string $url, string $media = 'all') 
    {
        $this->styles[] = ['url' => $url, 'media' => $media];
    }


    public function RegisterScript(string $url, bool $defer = false, bool $async = false, string $type = '') 
    {

        $this->scripts[] = [
            'url' => $url,
            'defer' => $defer,
            'async' => $async,
            'type' => $type
        ];
    }

    public function SetTitle(string $title)
    {
        $this->title = $title;
    }

    public function GetStyles() 
    {
        return $this->styles;
    }

    public function GetScripts() 
    {
        return $this->scripts;
    }

    public function GetContent() 
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