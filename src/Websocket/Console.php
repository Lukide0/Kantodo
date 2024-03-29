<?php

declare(strict_types=1);

namespace Kantodo\Websocket;

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
        $size = memory_get_usage($real);
        $unit = array('B', 'KB', 'MB', 'GB', 'TB', 'PB');
        $usingRAM = @round($size / pow(1024, ($i = floor(log($size, 1024)))), 2) . ' ' . $unit[$i];
        Console::log("Memory usage: " . $usingRAM);
    }
}
