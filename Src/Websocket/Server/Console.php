<?php

declare(strict_types = 1);

namespace Kantodo\Websocket\Server;

class Console
{

    /**
     * Log
     *
     * @param   string  $message
     *
     * @return  void
     */
    public static function log(string $message)
    {
        echo "\033[36m$message \033[0m\n";
    }

    /**
     * Varování
     *
     * @param   string  $message
     *
     * @return  void
     */
    public static function warning(string $message)
    {
        echo "\033[33mWARNING: $message \033[0m\n";
    }

    /**
     * Error
     *
     * @param   string  $message
     *
     * @return  void
     */
    public static function error(string $message)
    {
        echo "\033[31mERROR: $message \033[0m\n";
    }

    /**
     * Zobrazí používanou paměť
     *
     * @param   bool  $real
     *
     * @return  void
     */
    public static function memory(bool $real = false)
    {
        Console::log(memory_get_usage($real) / 1024 . " KB");
    }
}
