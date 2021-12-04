<?php

declare(strict_types = 1);

namespace Kantodo\Models;

use Kantodo\Core\Application;
use Kantodo\Core\Base\Model;
use Kantodo\Core\Database\Connection;
use Kantodo\Core\Generator;
use PDO;

/**
 * Model na projekt
 */
class ProjectModel extends Model
{

    /**
     * Pozice
     *
     * @var array<string,array<string,bool>>
     */
    const POSITIONS = [

        'admin'           => [
            'editProjectSettings'  => true,

            'addTask'              => true,
            'editTask'             => true,
            'removeTask'           => true,
            'canCloseTask'         => true,

            'addPeople'            => true,
            'removePeople'         => true,
            'changePeoplePosition' => true,
        ],
        'user'        => [
            'editProjectSettings'  => false,

            'addTask'              => true,
            'editTask'             => true,
            'removeTask'           => true,
            'canCloseTask'         => true,

            'addPeople'            => false,
            'removePeople'         => false,
            'changePeoplePosition' => false,
        ],
        'viewer'            => [
            'editProjectSettings'  => false,

            'addTask'              => false,
            'editTask'             => false,
            'removeTask'           => false,
            'canCloseTask'         => false,

            'addPeople'            => false,
            'removePeople'         => false,
            'changePeoplePosition' => false,
        ],

    ];

    public function __construct()
    {
        parent::__construct();
        $this->table = Connection::formatTableName('projects');
        
        $this->setColumns([
            'project_id',
            'name',
            'is_open',
            'uuid',
            'is_public',
        ]);
    }
    
    /**
     * Smaže projekt
     *
     * @param   int  $projID    id projektu
     *
     * @return  bool            status
     */
    public function delete(int $projID)
    {
        $sth    = $this->con->prepare("DELETE FROM {$this->table} WHERE project_id = :projID");
        $status = $sth->execute([
            ":projID" => $projID,
        ]);
        
        return $status;
    }
    
    /**
     * Vrátí pravomoce pozice
     *
     * @param   string  $name       klíč pozice
     *
     * @return  array<string,bool>|false         vrací false pokud pozice neexistuje
     */
    public function getPositionPriv(string $name)
    {
        return self::POSITIONS[$name] ?? false;
    }
    
    
    /**
     * Vytvoří projekt
     *
     * @param   int     $userID  id tvůrce
     * @param   string  $name    název projektu
     *
     * @return  array<string,mixed>|false        vrací id a UUID záznamu nebo false pokud se nepovedlo vložit do databáze
     */
    public function create(int $userID, string $name)
    {
        $uuid = Generator::uuidV4();
        
        $query  = "INSERT INTO {$this->table} (`name`, `is_open`, `uuid`, `is_public`) VALUES (:name, '1', :uuid, '0')";
        $sth    = $this->con->prepare($query);
        $status = $sth->execute([
            ':name'   => $name,
            ':uuid'   => $uuid,
        ]);
        
        // podařilo se vytvořit projekt
        if ($status === true) {
            $projID = (int)$this->con->lastInsertId();
            $posID  = $this->getPosition('admin');
            
            // neexistuje pozice admin v databázi
            if ($posID === false) {
                $posID = $this->createPosition('admin');
                
                if ($posID === false) {
                    // smaže projekt
                    $this->delete($projID);
                    return false;
                }
            }
            
            // vytvoří admina
            $status = $this->setUserPosition($userID, $projID, $posID);
            
            // nepodařilo se vytvořit admina
            if ($status === false) {
                //smaže projekt
                $this->delete($projID);
                return false;
            }
            
            return ['id' => $projID, 'uuid' => $uuid];
        }
        
        return false;
    }
    
    /**
     * Vrací id pozice podle jména
     *
     * @param   string  $name  jméno pozice
     *
     * @return  int|false      vrací false pokud pozice neexistuje nebo pokud se nepodařilo získat id
     */
    public function getPosition(string $name)
    {
        $projPos = Connection::formatTableName('project_positions');
        
        $sth = $this->con->prepare("SELECT `project_position_id` FROM {$projPos} WHERE `name` = :name LIMIT 1");
        
        $status = $sth->execute([
            ':name' => $name,
        ]);
        
        if ($status === true) {
            $result = $sth->fetch(PDO::FETCH_ASSOC);
            
            if ($result !== false && count($result) != 0) {
                return (int)$result['project_position_id'];
            }
            
            return false;
        }
        
        return false;
    }
    
    /**
     * Vrací id projektu a název pozice podle id uživatele a uuid projektu
     *
     * @param   int  $userID  id uživatele
     * @param   string $projUUID uuid projektu
     *
     * @return  array<string,string>|false      vrací false pokud pozice neexistuje nebo pokud se nepodařilo získat id
     */
    public function getBaseDetails(int $userID, string $projUUID)
    {
        $projPos = Connection::formatTableName('project_positions');
        $userProj = Connection::formatTableName('user_projects');
        $sth = $this->con->prepare(<<<SQL
        SELECT
            proj_pos.name,
            proj.project_id as id
        FROM 
            {$userProj} as user_proj
        INNER JOIN 
            {$this->table} as proj
        ON
            user_proj.project_id = proj.project_id
        INNER JOIN
            {$projPos} as proj_pos
        ON
            user_proj.project_position_id = proj_pos.project_position_id
        WHERE
            proj.uuid = :uuid AND user_proj.user_id = :userID
        SQL);
        
        $status = $sth->execute([
            ':userID'   => $userID,
            ':uuid'   => $projUUID,
        ]);
        
        if ($status === true) {
            $result = $sth->fetch(PDO::FETCH_ASSOC);
            
            if ($result !== false && count($result) != 0) {
                return $result;
            }
            
            return false;
        }
        
        return false;
        
    }
    
    /**
     * Nastaví uživateli pozici v projektu
     *
     * @param   int  $userID  id uživatele
     * @param   int  $projID  id projektu
     * @param   int  $posID   id pozice
     *
     * @return  int|false     vrací id vloženého záznamu nebo false pokud se nepodařilo vložit
     */
    public function setUserPosition(int $userID, int $projID, int $posID)
    {
        $userProj = Connection::formatTableName('user_projects');
        $sth      = $this->con->prepare("INSERT INTO {$userProj} (`user_id`, `project_id`, `project_position_id`) VALUES ( :userID, :projID, :posID)");
        $status   = $sth->execute([
            ':userID' => $userID,
            ':projID' => $projID,
            ':posID'  => $posID,
        ]);
        
        return ($status === true) ? (int)$this->con->lastInsertId() : false;
    }
    
    /**
     * Vytvoří pozici v databázi
     *
     * @param   string  $name  název pozice
     *
     * @return  int|false      vrací id vloženého záznamu nebo false pokud se nepodařilo vložit
     */
    public function createPosition(string $name)
    {
        $projPos = Connection::formatTableName('project_positions');
        
        $query = <<<SQL
        INSERT INTO {$projPos} ( `name` ) VALUES ( :name )
        SQL;
        
        $sth    = $this->con->prepare($query);
        $status = $sth->execute([
            ':name' => $name,
        ]);
        
        return ($status === true) ? (int)$this->con->lastInsertId() : false;
    }
    
    /**
     * Vrací jméno pozice
     *
     * @param   int  $projID      id projektu
     * @param   int  $userID      id uživatele
     *
     * @return  string|false      vrací false pokud se nepodařilo získat pozici nebo uživatel není v projektu
     */
    public function getProjectPosition(int $projID, int $userID)
    {
        $projPos  = Connection::formatTableName('project_positions');
        $userProj = Connection::formatTableName('user_projects');
        
        $query = <<<SQL
        SELECT
            proj_pos.name
        FROM {$userProj} as user_proj
            INNER JOIN {$projPos} as proj_pos
                ON user_proj.project_position_id = proj_pos.project_position_id
        WHERE user_proj.project_id = :projID AND user_proj.user_id = :userID
        SQL;
        
        $sth    = $this->con->prepare($query);
        $status = $sth->execute([
            ':projID'     => $projID,
            ':userID' => $userID,
        ]);
        
        if ($status === true) {
            $pos = $sth->fetch(PDO::FETCH_ASSOC);
            
            if (empty($pos)) {
                return false;
            }
            
            return $pos['name'];
        }
        
        return false;
    }
    
    /**
     * Vrací iniciály členů týmu
     *
     * @param   int  $projID  id projektu
     *
     * @return  array<mixed>|false      iniciály
     */
    public function getMembersInitials(int $projID)
    {
        $users    = Connection::formatTableName('users');
        $userProj = Connection::formatTableName('user_projects');
        
        $query = <<<SQL
        SELECT
            CONCAT(
                LEFT(u.firstname, 1),
                LEFT(u.lastname, 1)
            ) AS `initials`
        FROM
            {$userProj} AS user_proj
        INNER JOIN {$users} AS u
        ON
            user_proj.user_id = u.user_id
        WHERE
            user_proj.project_id = :projID
        SQL;
        
        $sth    = $this->con->prepare($query);
        $status = $sth->execute([
            ':projID' => $projID,
        ]);
        
        if ($status === true) {
            return $sth->fetchAll(PDO::FETCH_COLUMN);
        }
        
        return false;
    }
    
    /**
     * Zjistí jestli uživatel patří do projektu
     *
     * @param   int     $userID       id uživatele
     * @param   string  $projectUUID  uuid projektu
     *
     * @return  int|false                 pokud existuje vrací id projektu jinak vrací false
     */
    public function projectMember(int $userID, string $projectUUID)
    {
        $userProj = Connection::formatTableName('user_projects');

        $query = <<<SQL
        SELECT 
            p.project_id
        FROM 
            {$this->table} as p
        INNER JOIN 
            {$userProj} as up
        ON 
            up.project_id = p.project_id
        WHERE 
            up.user_id = :userID AND p.uuid = :uuid
        LIMIT 1
        SQL;

        $sth    = $this->con->prepare($query);
        $status = $sth->execute([
            ':userID' => $userID,
            ':uuid' => $projectUUID
        ]);

        if ($status === true) {
            $result = $sth->fetch(PDO::FETCH_ASSOC);
            if ($result != false && count($result) != 0)
                return $result['project_id'];
            else
                return false;
        } else {
            return false;
        }
    }

    /**
     * Vrací projekty ve kterých je uživatel
     *
     * @param   int  $userID  id uživatele
     *
     * @return  array<mixed>|false  projekty
     */
    public function getUserProjects(int $userID)
    {
        $userProj = Connection::formatTableName('user_projects');
        
        
        $query = <<<SQL
        SELECT 
            p.name,
            p.uuid,
            p.project_id
        FROM 
            {$this->table} as p
        INNER JOIN 
            {$userProj} as up
        ON 
            up.project_id = p.project_id
        WHERE 
            up.user_id = :userID
        SQL;
        
        $sth    = $this->con->prepare($query);
        $status = $sth->execute([
            ':userID' => $userID,
        ]);
        
        if ($status === true) {
            return $sth->fetchAll(PDO::FETCH_ASSOC);
        }
        return false;
        
    }

    //---------------------//
    //-- Statické metody --//
    //---------------------//

    /**
     * Zjistí jestli má uživatel přístup k akci v projektu
     *
     * @param   string  $key          akce viz. POSITIONS
     * @param   int     $userID       id uživatele
     * @param   string  $projectUUID  UUID projektu
     *
     * @return  bool|null             Vrací false v případě, že akce neexistuje nebo uživatel nemá přístup k akci 
     */
    public static function hasPrivTo(string $key, int $userID, string $projectUUID)
    {
        if (!array_key_exists($key, self::POSITIONS))
            return null;

        $projModel = new ProjectModel();

        $details = $projModel->getBaseDetails($userID, $projectUUID);
        if ($details === false) 
            return false;

        $priv = $projModel->getPositionPriv($details['name']);

        if ($priv === false || !$priv[$key]) 
            return false;
        return true;
    }
}
