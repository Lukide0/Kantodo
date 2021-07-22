<?php


namespace Kantodo\Core\Database\Migration;

use Kantodo\Core\Application;
use Kantodo\Core\Database\Connection;
use Kantodo\Core\Database\Migration\Exception\MigrationException;

class Runner
{
    private $version = NULL;
    private $installVersion = NULL;
    /**
     * @var Schema
     */
    private $schema;
    const MIG_UP = -1;
    const MIG_DOWN = 1;
    const MIG_STAY = 0;

    public function __construct(bool $loadSchema = true, string $setCurrentVer = "") {
        $this->installVersion = $this->getInstallVersion();

        if ($setCurrentVer !== "")
            $this->version = $setCurrentVer;
        else
            $this->version = $this->getCurrentVersion();
            

        if ($loadSchema)
            $this->schema = AbstractMigration::loadSchema();
        else
            $this->schema = new Schema(Application::$DB_TABLE_PREFIX);
    }

    public function run(string $version, bool $execute = true, bool $outputFile = false)
    {
        $mode = $this->compareVersions($this->version, $version);

        if ($mode == self::MIG_STAY)
            return;

        $versions = $this->getAllVersions();

        uksort($versions, [$this, 'CompareVersions']);

        $between = [];
        $tmp = 0;
        foreach ($versions as $ver => $_) {

            $fullname = "\Migrations\\Version_{$ver}";
            if ($ver == $version || $ver == $this->version)
            {
                if (!class_exists($fullname))
                    throw new MigrationException("`Version_{$ver}` is not a class");

                $tmp++;

                if (($ver == $version && $mode == self::MIG_UP) || ($ver == $this->version && $mode == self::MIG_DOWN))
                    $between[] = $ver;


                continue;
            }
    
            if ($tmp == 1) 
            {
                if (!class_exists($fullname))
                    throw new MigrationException("`Version_{$ver}` is not a class");
                $between[] = $ver;
            }

            if ($tmp == 2)
                break;
        }

        if ($mode == self::MIG_DOWN) 
        {
    
            for ($index = count($between) - 1; $index >= 0; $index--) { 
                $mig = '\Migrations\\Version_' . $between[$index];

                /**
                 * @var Migration
                 */
                $instance = new $mig($this->schema);
                $instance->down($this->schema);
            }           
        }

        if ($mode == self::MIG_UP) 
        {
            for ($index = 0; $index < count($between); $index++) { 
                $mig = '\Migrations\\Version_' . $between[$index];

                /**
                 * @var Migration
                 */
                $instance = new $mig($this->schema);
                $instance->up($this->schema);
            }
        }

        if ($execute) 
        {
            Connection::runInTransaction($this->schema->getQueries());
            AbstractMigration::saveSchema($this->schema);
            $this->updateConfigVersion($version);
        }

        if ($outputFile) 
        {
            $sql = $this->schema->getSQL();

            $output = Application::$ROOT_DIR . "/Migrations/Versions/{$version}.sql";

            file_put_contents($output, $sql);
        }

    }

    public function getCurrentVersion() 
    {

        if (!file_exists(Application::$MIGRATION_DIR . '/config.json'))
            return '0_0';

        if ($this->version !== NULL)
            return $this->version;

        $json = json_decode(file_get_contents(Application::$MIGRATION_DIR . '/config.json'), true);
        return $this->version = str_replace('.', '_', $json['version']);
    }

    public function getInstallVersion() 
    {
        if (!file_exists(Application::$MIGRATION_DIR . '/info.json'))
            throw new MigrationException('Info.json does not exist');

        if ($this->installVersion !== NULL)
            return $this->installVersion;

        $json = json_decode(file_get_contents(Application::$MIGRATION_DIR . '/info.json'), true);

        return $this->installVersion = str_replace('.', '_', $json['install_version']);
    }

    public function getAllVersions() 
    {
        $pattern = Application::$MIGRATION_DIR . '/Versions/Version_*.php';
        $valid = [];
        foreach (glob($pattern) as $file) {
            $file = str_replace(Application::$MIGRATION_DIR . '/Versions/', '', $file);       
            if (preg_match("/^Version_(?<version>[0-9]+(_[0-9]+)*)\.php$/", $file, $matches)) 
            {
                $valid[$matches['version']] = $file;
            }
        }
        return $valid;
    }

    public function updateConfigVersion(string $version) 
    {
        $json = json_decode(file_get_contents(Application::$MIGRATION_DIR . '/config.json'), true);
        $json['version'] = str_replace('_', '.', $version);

        file_put_contents(Application::$MIGRATION_DIR . '/config.json',json_encode($json, JSON_PRETTY_PRINT));

    }

    public function compareVersions(string $a, string $b)
    {
        $a = explode('_', $a);
        $b = explode('_', $b);
        $sizeA = count($a);
        $sizeB = count($b);
        $i = 0;
        while ($sizeA > $i && $sizeB > $i) {
            $tmpA = intval($a[$i]);
            $tmpB = intval($b[$i]);

            if ($tmpA < $tmpB) 
            {
                return -1;
            }

            if ($tmpA > $tmpB) 
            {
                return 1;
            }

            $i++;
        }

        if ($sizeA < $sizeB) return -1;
        if ($sizeA > $sizeB) return 1;
        
        return 0;
        
    }



}



?>