<?php 

namespace Kantodo\Core\Exception;

use Exception;

class KantodoException extends Exception
{
    public function __toString()
    {
        $className = get_class($this);
        $info = $this->getTrace()[0];
        return $className . ": [{$this->code}]: {$this->message}\n in file '{$info['file']}' on line {$info['line']}";
    }
}


?>