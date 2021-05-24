<?php

namespace Kantodo\Core\Exception;

use \Exception;

class CustomException extends Exception
{
    protected string $message = "Unknown exception";
    protected int $code = 0;


    protected string $file;
    protected string $line;


    public function __construct($message = null, $code = 0)
    {
        if (!$message)
            throw new $this('Unknown '. get_class($this));

        parent::__construct($message, $code);
    }

    public function __toString()
    {
        return get_class($this) . " '{$this->message}' in {$this->file}({$this->line})\n" . "{$this->getTraceAsString()}";
    }
}



?>