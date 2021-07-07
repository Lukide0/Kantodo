<?php

namespace Kantodo\Core\Database;

use Kantodo\Core\Application;
use Kantodo\Core\Exception\ConfigException;
use PDO;

class Connection
{
    //Singleton pattern

    /**
     * @var \PDO
     */
    private static $instance = null;
    public static function GetInstance() 
    {
        if (self::$instance == null)
            return self::$instance = new Connection();
        return self::$instance;
    }

    public static function TryConnect(string $dns, string $username = null, string $password = null)
    {
        try {
            $con = new PDO($dns, $username, $password);
            
            $errorMode = (Application::$DEBUG_MODE) ? PDO::ERRMODE_EXCEPTION : PDO::ERRMODE_SILENT;
            
            $con->setAttribute(PDO::ATTR_ERRMODE, $errorMode);
        } catch (\Throwable $th) {
            return false;
        }
        
        return true;
    }
    
    /**
     * @var \PDO
     */
    protected $con;
    
    private final function __construct()
    {
        if (!Application::$CONFIG_LOADED)
            throw new ConfigException("Config is not loaded");

        $dns = "mysql:host=" . DB_HOST . ";dbname=" . DB_TABLE;
        
        try {
            $this->con = new PDO($dns, DB_USER, DB_PASS, array(
                PDO::ATTR_PERSISTENT => true,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8",
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET CHARACTER SET utf8",
            ));
    
            $errorMode = (Application::$DEBUG_MODE) ? PDO::ERRMODE_EXCEPTION : PDO::ERRMODE_SILENT;
    
            $this->con->setAttribute(PDO::ATTR_ERRMODE, $errorMode);
        } catch (\Throwable $th) {
           $this->con = null;
        }

        self::$instance = $this->con;
    }

    public static function RunInTransaction($callback)
    {
        self::$instance->beginTransaction();
        
        $commit = call_user_func($callback);
        
        if ($commit) 
        {
            self::$instance->commit();
            return true;
        }

        self::$instance->rollBack();
        return false;

    }

    public function GetConnection() 
    {
        return $this->con;
    }

}



?>