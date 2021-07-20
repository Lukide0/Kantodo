<?php 

namespace Kantodo\Core;


abstract class AbstractWidget 
{
    private $options = [];

    public final function setOption(string $name, $value)
    {
        $this->options[$name] = $value;
    }

    public final function getOption(string $name, $fallback = NULL)
    {
        return $this->options[$name] ?? $fallback;
    }

    public abstract function getHTML();
}

?>