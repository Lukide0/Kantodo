<?php

namespace Kantodo\Core;

use Kantodo\Core\Database\Connection;

abstract class Model
{
    protected $con;
    protected $table;
    private function __clone() { }
    
    public function __construct() {
        $this->con = Connection::getInstance();
    }
}



?>