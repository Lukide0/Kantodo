<?php

namespace Kantodo\Core\Base;

/**
 * Základ widget
 */
abstract class AbstractWidget
{
    /**
     * nastavení
     *
     * @var array
     */
    private $options = [];

    /**
     * Nastaví nastavení
     *
     * @param   string  $name   klíč
     * @param   mixed   $value  hodnota
     *
     * @return  void
     */
    final public function setOption(string $name, $value)
    {
        $this->options[$name] = $value;
    }

    /**
     * Získá nastavení
     *
     * @param   string  $name      klíč
     * @param   mixed  $fallback   pokud neexistuje, tak vrátí tuto hodnotu
     *
     * @return  mixed
     */
    final public function getOption(string $name, $fallback = null)
    {
        return $this->options[$name] ?? $fallback;
    }

    /**
     * Vrátí html widget
     *
     * @return  string
     */
    abstract public function getHTML();
}
