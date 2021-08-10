<?php

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
     * @var array
     */
    const POSITIONS = [

        'admin'           => [
            'editProjectSettings'  => true,

            'addTask'              => true,
            'editTask'             => true,
            'removeTask'           => true,
            'canCloseTask'         => true,

            'addMilestone'         => true,
            'editMilestone'        => true,
            'removeMilestone'      => true,

            'addColumn'            => true,
            'editColumn'           => true,
            'removeColumn'         => true,

            'addPeople'            => true,
            'removePeople'         => true,
            'changePeoplePosition' => true,
        ],
        'project_manager' => [
            'editProjectSettings'  => true,

            'addTask'              => true,
            'editTask'             => true,
            'removeTask'           => true,
            'canCloseTask'         => true,

            'addColumn'            => true,
            'editColumn'           => true,
            'removeColumn'         => true,

            'addMilestone'         => true,
            'editMilestone'        => true,
            'removeMilestone'      => true,

            'addPeople'            => true,
            'removePeople'         => true,
            'changePeoplePosition' => true,
        ],
        'assignor'        => [
            'editProjectSettings'  => false,

            'addTask'              => true,
            'editTask'             => true,
            'removeTask'           => true,
            'canCloseTask'         => true,

            'addColumn'            => false,
            'editColumn'           => false,
            'removeColumn'         => false,

            'addMilestone'         => true,
            'editMilestone'        => true,
            'removeMilestone'      => true,

            'addPeople'            => false,
            'removePeople'         => false,
            'changePeoplePosition' => false,
        ],
        'reviewer'        => [
            'editProjectSettings'  => false,

            'addTask'              => false,
            'editTask'             => false,
            'removeTask'           => false,
            'canCloseTask'         => true,

            'addColumn'            => false,
            'editColumn'           => false,
            'removeColumn'         => false,

            'addMilestone'         => false,
            'editMilestone'        => false,
            'removeMilestone'      => false,

            'addPeople'            => false,
            'removePeople'         => false,
            'changePeoplePosition' => false,
        ],
        'user'            => [
            'editProjectSettings'  => false,

            'addTask'              => false,
            'editTask'             => false,
            'removeTask'           => false,
            'canCloseTask'         => false,

            'addColumn'            => false,
            'editColumn'           => false,
            'removeColumn'         => false,

            'addMilestone'         => false,
            'editMilestone'        => false,
            'removeMilestone'      => false,

            'addPeople'            => false,
            'removePeople'         => false,
            'changePeoplePosition' => false,
        ],

    ];

    public function __construct()
    {
        parent::__construct();
        $this->table = Connection::formatTableName('projects');
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
     * @return  array|false         vrací false pokud pozice neexistuje
     */
    public function getPositionPriv(string $name)
    {
        return self::POSITIONS[$name] ?? false;
    }

    /**
     * Vytvoří projekt
     *
     * @param   int     $teamID  id týmu
     * @param   int     $userID  id tvůrce
     * @param   string  $name    název projektu
     *
     * @return  int|false        vrací id záznamu nebo false pokud se nepovedlo vložit do databáze
     */
    public function create(int $teamID, int $userID, string $name)
    {
        $uuid = Generator::uuidV4();

        $query  = "INSERT INTO {$this->table} (`name`, `team_id`, `is_open`, `uuid`, `is_public`) VALUES (:name, :teamID, '1', :uuid, '1')";
        $sth    = $this->con->prepare($query);
        $status = $sth->execute([
            ':name'   => $name,
            ':teamID' => $teamID,
            ':uuid'   => $uuid,
        ]);

        // podařilo se vytvořit projekt
        if ($status === true) {
            $projID = $this->con->lastInsertId();
            $posID  = $this->getPosition('admin');

            // neexistuje pozice admin v databázi
            if ($posID === false) {
                // smaže projekt
                $this->delete($projID);
                return false;
            }

            $userTeamID = Application::$APP->session->get($teamID)['id'];

            // vytvoří admina
            $status = $this->setUserPosition($userTeamID, $projID, $posID);

            // nepodařilo se vytvořit admina
            if ($status === false) {
                //smaže projekt
                $this->delete($projID);
                return false;
            }

            return $projID;
        }

        return false;
    }

    /**
     * Vrací všechny sloupce v projektu
     *
     * @param   int  $projID    id projektu
     *
     * @return  array|false     vrací false pokud se nepodařilo získat sloupce
     */
    public function getColumns(int $projID)
    {
        $columns = Connection::formatTableName('columns');
        $query   = <<<SQL
        SELECT
            `column_id` as `id`,
            `name`,
            `max_task_count`
        FROM
            {$columns}
        WHERE
            `project_id` = :projID
        SQL;

        $sth = $this->con->prepare($query);

        $status = $sth->execute([
            ':projID' => $projID,
        ]);

        if ($status === true) {
            return $sth->fetchAll(PDO::FETCH_ASSOC);
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
        $teamPos = Connection::formatTableName('project_positions');

        $sth = $this->con->prepare("SELECT `project_position_id` FROM {$teamPos} WHERE `name` = :name LIMIT 1");

        $status = $sth->execute([
            ':name' => $name,
        ]);

        if ($status === true) {
            $result = $sth->fetch(PDO::FETCH_ASSOC);

            if (count($result) != 0) {
                return $result['project_position_id'];
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
        $userProj = Connection::formatTableName('user_team_projects');
        $sth      = $this->con->prepare("INSERT INTO {$userProj} (`user_team_id`, `project_id`, `project_position_id`) VALUES ( :userID, :projID, :posID)");
        $status   = $sth->execute([
            ':userID' => $userID,
            ':projID' => $projID,
            ':posID'  => $posID,
        ]);

        return ($status === true) ? $this->con->lastInsertId() : false;
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
        $teamPos = Connection::formatTableName('project_positions');

        $query = <<<SQL
        INSERT INTO {$teamPos} ( `name` ) VALUES ( :name )
        SQL;

        $sth    = $this->con->prepare($query);
        $status = $sth->execute([
            ':name' => $name,
        ]);

        return ($status === true) ? $this->con->lastInsertId() : false;
    }

    /**
     * Vrací jméno pozice
     *
     * @param   int  $projID      id projektu
     * @param   int  $userTeamID  id z tabulky `user_team_projects`
     *
     * @return  string|false      vrací false pokud se nepodařilo ji získat nebo uživatel není v projektu
     */
    public function getProjectPosition(int $projID, int $userTeamID)
    {
        $projPos  = Connection::formatTableName('project_positions');
        $userProj = Connection::formatTableName('user_team_projects');

        $query = <<<SQL
        SELECT
            proj_pos.name
        FROM {$userProj} as up
            INNER JOIN {$projPos} as proj_pos
                ON up.project_position_id = proj_pos.project_position_id
        WHERE up.project_id = :projID AND up.user_team_id = :userTeamID
        SQL;

        $sth    = $this->con->prepare($query);
        $status = $sth->execute([
            ':projID'     => $projID,
            ':userTeamID' => $userTeamID,
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
     * Vrací jméno pozice
     *
     * @param   int  $projID  id projektu
     * @param   int  $userID  id uživatele
     *
     * @return  string|false  vrací false pokud se nepodařilo získat pozici nebo uživatel není v projektu
     */
    public function getUserProjectPosition(int $projID, int $userID)
    {
        $projPos  = Connection::formatTableName('project_positions');
        $userTeam = Connection::formatTableName('user_teams');
        $userProj = Connection::formatTableName('user_team_projects');

        $query = <<<SQL
        SELECT
            pp.name
        FROM
            {$userTeam} AS ut
        INNER JOIN {$userProj} AS utp
        ON
            utp.user_team_id = ut.user_team_id
        INNER JOIN {$projPos} AS pp
        ON
            pp.project_position_id = utp.project_position_id
        WHERE utp.project_id = :projID AND ut.user_id = :userID
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
     * Vrací iniciály členů týmu
     *
     * @param   int  $projID  id projektu
     *
     * @return  string[]      iniciály
     */
    public function getMembersInitials(int $projID)
    {
        $users    = Connection::formatTableName('users');
        $userTeam = Connection::formatTableName('user_teams');
        $userProj = Connection::formatTableName('user_team_projects');

        $query = <<<SQL
        SELECT
            CONCAT(
                LEFT(u.firstname, 1),
                LEFT(u.lastname, 1)
            ) AS `initials`
        FROM
            {$userProj} AS up
        INNER JOIN {$userTeam} AS ut
        ON
            ut.user_team_id = up.user_team_id
        INNER JOIN {$users} AS u
        ON
            ut.user_id = u.user_id
        WHERE
            up.project_id = :projID
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
     * Vrací všechny projekty v týmu i se statístikami úkolů
     *
     * @param   int  $teamID  id týmu
     *
     * @return  array         týmy
     */
    public function getTeamProjectList(int $teamID)
    {
        $columnsTable = Connection::formatTableName('columns');
        $tasksTable   = Connection::formatTableName('tasks');

        $query = <<<SQL
            SELECT proj.project_id,
                proj.name,
                proj.is_open,
                Sum(task.completed) AS task_completed,
                Sum(CASE
                        WHEN task.completed = 0 THEN 1
                        ELSE 0
                    END) AS task_not_completed
            FROM {$this->table} AS proj
                LEFT JOIN {$columnsTable} AS col
                        ON col.project_id = proj.project_id
                LEFT JOIN {$tasksTable} AS task
                        ON task.column_id = col.column_id
            WHERE proj.team_id = :teamID
            GROUP BY proj.project_id;
        SQL;

        $sth    = $this->con->prepare($query);
        $status = $sth->execute([
            ':teamID' => $teamID,
        ]);

        if ($status === true) {
            return $sth->fetchAll(PDO::FETCH_ASSOC);
        }

        return false;
    }
}
