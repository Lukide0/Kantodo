<?php

declare (strict_types = 1);

namespace Kantodo\Models;

use DateInterval;
use DateTime;
use DateTimeZone;
use Kantodo\Core\Base\Model;
use Kantodo\Core\Database\Connection;
use Kantodo\Core\Generator;
use Kantodo\Core\Validation\DataType;
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

        'admin'  => [
            'viewTask'             => true,
            'addTask'              => true,
            'editTask'             => true,
            'removeTask'           => true,
            
            'addOrRemovePeople'    => true,
            'changePeoplePosition' => true
        ],
        'user'   => [           
            'viewTask'             => true,
            'addTask'              => true,
            'editTask'             => true,
            'removeTask'           => true,

            'addOrRemovePeople'    => false,
            'changePeoplePosition' => false

        ],
        'viewer' => [
            'viewTask'             => true,
            'addTask'              => false,
            'editTask'             => false,
            'removeTask'           => false,

            'addOrRemovePeople'    => false,
            'changePeoplePosition' => false

        ],
        'guest' => [
            'viewTask'             => false,
            'addTask'              => false,
            'editTask'             => false,
            'removeTask'           => false,

            'addOrRemovePeople'    => false,
            'changePeoplePosition' => false

        ]
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
            ':name' => $name,
            ':uuid' => $uuid,
        ]);

        // podařilo se vytvořit projekt
        if ($status === true) {
            $projID = (int) $this->con->lastInsertId();
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
     * Nastaví pozici uživateli
     *
     * @param   int     $userID     id uživatele
     * @param   int     $projectID  id projektu
     * @param   string  $name       název pozice
     *
     * @return  int|false           id záznamu
     */
    public function setPosition(int $userID, int $projectID, string $name)
    {
        if (isset(self::POSITIONS[$name]) === false) 
        {
            return false;
        }
        $posID  = $this->getPosition($name);

        // neexistuje pozice v databázi
        if ($posID === false) {
            $posID = $this->createPosition($name);
            if ($posID === false) {
                return false;
            }
        }
        return $this->setUserPosition($userID, $projectID, $posID);
    }


    /**
     * Upraví pozici uživateli
     *
     * @param   int     $userID  id uživatele
     * @param   int     $projID  id projektu
     * @param   string  $name    název pozice
     *
     * @return  bool           status
     */
    public function updatePosition(int $userID, int $projID, string $name)
    {
        if (isset(self::POSITIONS[$name]) === false) 
        {
            return false;
        }
        $posID  = $this->getPosition($name);

        // neexistuje pozice v databázi
        if ($posID === false) {
            $posID = $this->createPosition($name);
            if ($posID === false) {
                return false;
            }
        }

        $userProj = Connection::formatTableName('user_projects');
        $sth      = $this->con->prepare("UPDATE {$userProj} SET project_position_id = :posID WHERE user_id = :userID AND project_id = :projID");
        $status   = $sth->execute([
            ':userID' => $userID,
            ':projID' => $projID,
            ':posID'  => $posID,
        ]);

        return $status;
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
                return (int) $result['project_position_id'];
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
        $projPos  = Connection::formatTableName('project_positions');
        $userProj = Connection::formatTableName('user_projects');
        $sth      = $this->con->prepare(<<<SQL
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
            ':userID' => $userID,
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

        return ($status === true) ? (int) $this->con->lastInsertId() : false;
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

        return ($status === true) ? (int) $this->con->lastInsertId() : false;
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
            ':projID' => $projID,
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
     * Vrací členy týmu
     *
     * @param   int  $projID  id projektu
     *
     * @return  array<mixed>|false      jména
     */
    public function getMembers(int $projID)
    {
        $users    = Connection::formatTableName('users');
        $userProj = Connection::formatTableName('user_projects');

        $query = <<<SQL
        SELECT
            u.firstname,
            u.lastname,
            u.email,
            user_proj.project_position_id
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
            return $sth->fetchAll(PDO::FETCH_ASSOC);
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
            ':uuid'   => $projectUUID,
        ]);

        if ($status === true) {
            $result = $sth->fetch(PDO::FETCH_ASSOC);
            if ($result != false && count($result) != 0) {
                return $result['project_id'];
            } else {
                return false;
            }

        } else {
            return false;
        }
    }

    /**
     * Vrátí pozice podle ids
     *
     * @param   array<int>  $ids  ids
     *
     * @return  array<int,string>|false  pozice
     */
    public function getPositionsByID(array $ids)
    {
        $projPos = Connection::formatTableName('project_positions');

        $sth = $this->con->prepare("SELECT project_position_id, name FROM {$projPos} WHERE project_position_id IN (" . implode(',', $ids) .  ")");
        $status = $sth->execute();

        if ($status === true) {
            return $sth->fetchAll(PDO::FETCH_KEY_PAIR);
        }

        return false;
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

    /**
     * Získá kód projektu nebo ho vytvoří
     *
     * @param   string  $projectUUID  uuid projektu
     *
     * @return  string|false              kód
     */
    public function getOrCreateCode(string $projectUUID)
    {
        $projectCode = Connection::formatTableName('project_codes');

        $project = $this->getSingle(['project_id'], ['uuid' => $projectUUID]);

        if ($project === false) 
        {
            return false;
        }

        $projectID = (int)$project['project_id'];

        $query = <<<SQL
        SELECT
            project_code_id,
            code,
            expiration
        FROM 
            {$projectCode}
        WHERE project_id = :projectID LIMIT 1
        SQL;

        $sth    = $this->con->prepare($query);
        $status = $sth->execute([
            ':projectID' => $projectID,
        ]);

        $result = $sth->fetch(PDO::FETCH_ASSOC);

        $now = new DateTime("now", new DateTimeZone("UTC"));
        $tomorrow = (clone $now)->add(DateInterval::createFromDateString('+1 day'))->format(Connection::DATABASE_DATE_FORMAT);
        
        if ($status === true && $result !== false && count($result) != 0) 
        {
            $exp = DateTime::createFromFormat(Connection::DATABASE_DATE_FORMAT, $result['expiration']);

            if ($exp !== false && $exp->getTimestamp() > $now->getTimestamp()) 
            {
                return $result['code'];
            } else
            {
                $code = Generator::uuidV4();

                $update = "UPDATE {$projectCode} SET code = :code, expiration = :expiration WHERE project_code_id = :projCodeID";
                $sth = $this->con->prepare($update);
                $status = $sth->execute([
                    ':code' => $code,
                    ':expiration' =>  $tomorrow,
                    ':projCodeID' => $result['project_code_id']
                ]);

                if ($status === false)
                    return false;
                else 
                    return $code;
            }
        }

        $code = Generator::uuidV4();

        $insert = "INSERT INTO {$projectCode} (code, expiration, project_id) VALUE (:code, :expiration, :projectID)";
        $sth = $this->con->prepare($insert);
        $status = $sth->execute([
            ':code' => $code,
            ':expiration' => $tomorrow,
            ':projectID' => $projectID
        ]);

        if ($status === false)
            return false;
        else
            return $code;
    }

    /**
     * Odstraní uživatele z projektu
     *
     * @param   int  $userID     id uživatele
     * @param   int  $projectID  id projektu
     *
     * @return  bool
     */
    public function removeUser(int $userID, int $projectID)
    {
        $userProj = Connection::formatTableName("user_projects");
        $sth    = $this->con->prepare("DELETE FROM {$userProj} WHERE project_id = :projID AND user_id = :userID");
        $status = $sth->execute([
            ":projID" => $projectID,
            ":userID" => $userID
        ]);

        return $status;
    }


    /**
     * Získá projekt podle kódu
     *
     * @param   string  $code  kód
     *
     * @return  int|false id projektu
     */
    public function getProjectByCode(string $code)
    {
        $projectCode = Connection::formatTableName('project_codes');

        $query = "SELECT project_id FROM {$projectCode} WHERE code = :code AND expiration > :now LIMIT 1";

        $sth = $this->con->prepare($query);
        $status = $sth->execute([
            ':code' => $code,
            ':now'  => (new DateTime("now", new DateTimeZone("UTC")))->format(Connection::DATABASE_DATE_FORMAT)
        ]);

        if ($status === false)
            return false;
        else 
        {
            $result = $sth->fetch(PDO::FETCH_COLUMN);
            return ($result !== false && DataType::wholeNumber($result)) ? (int)$result : false;
        }
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
     * @param   int     $outProjectID reference na proměnou do které bude vloženo id projektu
     *
     * @return  bool|null             Vrací false v případě, že akce neexistuje nebo uživatel nemá přístup k akci
     */
    public static function hasPrivTo(string $key, int $userID, string $projectUUID, int &$outProjectID = 0)
    {
        $keys = [
            'viewTask',
            'addTask',
            'editTask',
            'removeTask',
            'addOrRemovePeople',
            'changePeoplePosition'
        ];

        
        if (!in_array($key, $keys, true)) {
            return false;
        }

        $projModel = new ProjectModel();
        $details = $projModel->getBaseDetails($userID, $projectUUID);
        if ($details === false) {
            return false;
        }


        $priv = $projModel->getPositionPriv($details['name']);

        if ($priv === false || !$priv[$key]) {
            return false;
        }

        $outProjectID = (int)$details['id'];

        return true;
    }
}
