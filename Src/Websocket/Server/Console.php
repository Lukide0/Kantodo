<?php

namespace Kantodo\Websocket\Server;

class Console
{
    public static function log($message)
    {
        echo "\033[36m$message \033[0m\n";
    }

    public static function warning($message)
    {
        echo "\033[33mWARNING: $message \033[0m\n";
    }

    public static function error($message)
    {
        echo "\033[31mERROR: $message \033[0m\n";
    }

    public static function memory(bool $real = false)
    {
        Console::log(memory_get_usage($real) / 1024 . " KB");
    }
}
