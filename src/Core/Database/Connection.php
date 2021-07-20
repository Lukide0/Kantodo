<?php

namespace Kantodo\Core\Database;

use InvalidArgumentException;
use Kantodo\Core\Application;
use Kantodo\Core\Exception\ConfigException;
use Kantodo\Core\Singleton;
use PDO;

class Connection
{

    private static $instance = NULL;

    /**
     * @return PDO
     */
    public static function getInstance() 
    {
        if (self::$instance == NULL)
            new self();
        return self::$instance;
    }
    private function __clone() { }
    
    
    private final function __construct()
    {
        if (!Application::$CONFIG_LOADED && Application::$INSTALLING == false)
            throw new ConfigException("Config is not loaded");
            
            $dns = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME;
            
            $con = new PDO($dns, DB_USER, DB_PASS, array(
                PDO::ATTR_PERSISTENT => true,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8",
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET CHARACTER SET utf8",
            ));
            
        $errorMode = (Application::$DEBUG_MODE) ? PDO::ERRMODE_EXCEPTION : PDO::ERRMODE_SILENT;        
        $con->setAttribute(PDO::ATTR_ERRMODE, $errorMode);
        self::$instance = $con;
        
    }

    public static function debugMode(bool $enable = true) 
    {
        $errorMode = ($enable) ? PDO::ERRMODE_EXCEPTION : PDO::ERRMODE_SILENT;
        self::getInstance()->setAttribute(PDO::ATTR_ERRMODE, $errorMode);
    }
    
    public static function tryConnect(string $dns, string $username = NULL, string $password = NULL)
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
    public static function runInTransaction($queries, array $data = [])
    {
        
        if (is_string($queries))
            $queries = [$queries];
        
        if (!is_array($queries))
            throw new InvalidArgumentException("Parameter \$queries is not string|array.");
        
        $errorMode = (Application::$DEBUG_MODE) ? PDO::ERRMODE_EXCEPTION : PDO::ERRMODE_SILENT;
        $con = self::getInstance();

        if ($errorMode != PDO::ERRMODE_EXCEPTION)
            $con->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        try {
            $con->beginTransaction();
            
            foreach ($queries as $query) {
                $pdoStatement = $con->prepare($query);
                $pdoStatement->execute($data);
            }

            $con->commit();

            if ($errorMode != PDO::ERRMODE_EXCEPTION)
                $con->setAttribute(PDO::ATTR_ERRMODE, $errorMode);


        } catch (\Throwable $th) {
            if ($errorMode != PDO::ERRMODE_EXCEPTION)
                $con->setAttribute(PDO::ATTR_ERRMODE, $errorMode);
            $con->rollBack();
            throw $th;
        }
    }

    public static function formatTableName(string $table) 
    {
        return Application::$DB_TABLE_PREFIX . $table;
    }
}



?>