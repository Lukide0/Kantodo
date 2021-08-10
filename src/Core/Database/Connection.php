<?php

namespace Kantodo\Core\Database;

use InvalidArgumentException;
use Kantodo\Core\Application;
use Kantodo\Core\Exception\ConfigException;
use PDO;

/**
 * Třída na připojení k databázi
 */
class Connection
{
    const DATABASE_DATE_FORMAT = 'Y-m-d H:i:s';

    ///////////////
    // SINGLETON //
    ///////////////

    /**
     * @var PDO
     */
    private static $instance = null;

    /**
     * @return PDO
     */
    public static function getInstance()
    {
        if (self::$instance == null) {
            new self();
        }

        return self::$instance;
    }
    private function __clone()
    {}

    /**
     * @throws ConfigException pokud není načten config.php a zároveň neprobíhá instalace
     */
    final private function __construct()
    {
        if (!Application::$CONFIG_LOADED && Application::$INSTALLING == false) {
            throw new ConfigException('Config is not loaded');
        }

        $dns = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME;

        $con = new PDO($dns, DB_USER, DB_PASS, array(
            // PDO::ATTR_PERSISTENT => true,
            PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8',
            PDO::MYSQL_ATTR_INIT_COMMAND => 'SET CHARACTER SET utf8',
        ));

        $errorMode = (Application::$DEBUG_MODE) ? PDO::ERRMODE_EXCEPTION : PDO::ERRMODE_SILENT;
        $con->setAttribute(PDO::ATTR_ERRMODE, $errorMode);
        self::$instance = $con;

    }

    /**
     * Nastaví PDO::ATTR_ERRMODE
     *
     * @param   bool  $enable  zapnout|vypnout error
     *
     * @return  void
     */
    public static function debugMode(bool $enable = true)
    {
        $errorMode = ($enable) ? PDO::ERRMODE_EXCEPTION : PDO::ERRMODE_SILENT;
        self::getInstance()->setAttribute(PDO::ATTR_ERRMODE, $errorMode);
    }

    /**
     * Pokus se připojit
     *
     * @param   string  $dns       dns
     * @param   string  $username  uživatelské jméno
     * @param   string  $password  heslo
     *
     * @return  bool               pokud se povedlo připojit
     */
    public static function tryConnect(string $dns, string $username = null, string $password = null)
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
     * Provede příkazy v transakci
     *
     * @param   string[] $queries  příkazy
     * @param   array  $data
     *
     * @return  void
     */
    public static function runInTransaction($queries, array $data = [])
    {

        if (is_string($queries)) {
            $queries = [$queries];
        }

        if (!is_array($queries)) {
            throw new InvalidArgumentException('Parameter $queries is not string|array.');
        }

        $errorMode = (Application::$DEBUG_MODE) ? PDO::ERRMODE_EXCEPTION : PDO::ERRMODE_SILENT;
        $con       = self::getInstance();

        if ($errorMode != PDO::ERRMODE_EXCEPTION) {
            $con->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        }

        try {
            $con->beginTransaction();

            foreach ($queries as $query) {
                $pdoStatement = $con->prepare($query);
                $pdoStatement->execute($data);
            }

            $con->commit();

            if ($errorMode != PDO::ERRMODE_EXCEPTION) {
                $con->setAttribute(PDO::ATTR_ERRMODE, $errorMode);
            }

        } catch (\Throwable $th) {
            if ($errorMode != PDO::ERRMODE_EXCEPTION) {
                $con->setAttribute(PDO::ATTR_ERRMODE, $errorMode);
            }

            $con->rollBack();
            throw $th;
        }
    }

    /**
     * Naformátuje jméno tabulky
     *
     * @param   string  $table  tabulka
     *
     * @return  string
     */
    public static function formatTableName(string $table)
    {
        return Application::$DB_TABLE_PREFIX . $table;
    }
}
