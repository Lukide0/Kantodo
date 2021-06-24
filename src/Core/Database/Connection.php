<?php

namespace Kantodo\Core\Database;

use Kantodo\Core\Application;
use PDO;

class Connection
{
    /**
     * @var \PDO
     */
    protected $con;
    public function Connect(string $dns, string $username = null, string $password = null) : bool
    {
        try {
            $this->con = new PDO($dns, $username, $password, array(
                PDO::ATTR_PERSISTENT => true,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8",
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET CHARACTER SET utf8",
            ));
    
            $errorMode = (Application::$DEBUG_MODE) ? PDO::ERRMODE_EXCEPTION : PDO::ERRMODE_SILENT;
    
            $this->con->setAttribute(PDO::ATTR_ERRMODE, $errorMode);
        } catch (\Throwable $th) {
           return false;
        }

        return true;
    }
    public static function TryConnect(string $dns, string $username = null, string $password = null)
    {
        try {
            $con = new PDO($dns, $username, $password, array(
                PDO::ATTR_PERSISTENT => true,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8",
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET CHARACTER SET utf8",
            ));
    
            $errorMode = (Application::$DEBUG_MODE) ? PDO::ERRMODE_EXCEPTION : PDO::ERRMODE_SILENT;
    
            $con->setAttribute(PDO::ATTR_ERRMODE, $errorMode);
        } catch (\Throwable $th) {
           return false;
        }

        return true;
    }
}



?>