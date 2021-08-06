<?php 

namespace Kantodo\Models;

use Kantodo\Core\Database\Connection;
use Kantodo\Core\Generator;
use Kantodo\Core\Model;
use PDO;

class ProjectModel extends Model
{
    const POSITIONS = [
        //NOTE: creator of project
        'admin' => [
            'editProjectSettings' => true,

            'addTask' => true,
            'editTask' => true,
            'removeTask' => true,
            'canCloseTask' => true,

            'addMilestone' => true,
            'editMilestone' => true,
            'removeMilestone' => true,

            'addPeople' => true,
            'removePeople' => true,
            'changePeoplePosition' => true
        ],
        'project_manager' => [
            'editProjectSettings' => true,

            'addTask' => true,
            'editTask' => true,
            'removeTask' => true,
            'canCloseTask' => true,

            'addMilestone' => true,
            'editMilestone' => true,
            'removeMilestone' => true,

            'addPeople' => true,
            'removePeople' => true,
            'changePeoplePosition' => true
        ],
        'assignor' => [
            'editProjectSettings' => false,

            'addTask' => true,
            'editTask' => true,
            'removeTask' => true,
            'canCloseTask' => true,

            'addMilestone' => true,
            'editMilestone' => true,
            'removeMilestone' => true,

            'addPeople' => false,
            'removePeople' => false,
            'changePeoplePosition' => false
        ],
        'reviewer' => [
            'editProjectSettings' => false,

            'addTask' => false,
            'editTask' => false,
            'removeTask' => false,
            'canCloseTask' => true,

            'addMilestone' => false,
            'editMilestone' => false,
            'removeMilestone' => false,

            'addPeople' => false,
            'removePeople' => false,
            'changePeoplePosition' => false
        ],
        'user' => [
            'editProjectSettings' => false,

            'addTask' => false,
            'editTask' => false,
            'removeTask' => false,
            'canCloseTask' => false,

            'addMilestone' => false,
            'editMilestone' => false,
            'removeMilestone' => false,

            'addPeople' => false,
            'removePeople' => false,
            'changePeoplePosition' => false
        ]

    ];

    public function __construct() {
        parent::__construct();
        $this->table = Connection::formatTableName('projects');
    }

    public function delete(int $projID)
    {
        $sth = $this->con->prepare("DELETE FROM {$this->table} WHERE project_id = :projID");
        $status = $sth->execute([
            ":projID" => $projID
        ]);

        return $status;
    }

    public function create(int $teamID, int $userID, string $name)
    {
        $uuid = Generator::uuidV4();

        $query = "INSERT INTO {$this->table} (`name`, `team_id`, `is_open`, `uuid`, `is_public`) VALUES (:name, :teamID, '1', :uuid, '1')";
        $sth = $this->con->prepare($query);
        $status = $sth->execute([
            ':name' => $name,
            ':teamID' => $teamID,
            ':uuid' => $uuid
        ]);

        if ($status === true) 
        {
            $projID = $this->con->lastInsertId();
            $posID = $this->getPosition('admin');

            if ($posID === false) 
            {
                $this->delete($projID);
                return false;
            }


            $status = $this->setUserPosition($userID, $projID, $posID);

            if ($status === false) 
            {
                $this->delete($projID);
                return false;
            }

            return $projID;

        }

        return false;
    }

    public function getColumns(int $projID)
    {
        $columns = Connection::formatTableName('columns');
        $query = <<<SQL
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
            ':projID' => $projID
        ]);
    
        if ($status === true) 
            return $sth->fetchAll(PDO::FETCH_ASSOC);
        return false;
    }

    public function getPosition(string $name)
    {
        $teamPos = Connection::formatTableName('project_positions');

        $sth = $this->con->prepare("SELECT `project_position_id` FROM {$teamPos} WHERE `name` = :name LIMIT 1");
        
        $status = $sth->execute([
            ':name' => $name
        ]);

        if ($status === true) 
        {
            $result = $sth->fetch(PDO::FETCH_ASSOC);

            if (count($result) != 0)
                return $result['project_position_id'];
            return false;

        }

        return false;
    }

    public function setUserPosition(int $userID, int $projID, int $posID)
    {
        $userProj = Connection::formatTableName('user_team_projects');
        $sth = $this->con->prepare("INSERT INTO {$userProj} (`user_team_id`, `project_id`, `project_position_id`) VALUES ( :userID, :projID, :posID)");
        $status = $sth->execute([
            ':userID' => $userID,
            ':projID' => $projID,
            ':posID' => $posID
        ]);

        return ($status === true) ? $this->con->lastInsertId() : false; 
    }

    public function createPosition(string $name)
    {
        $teamPos = Connection::formatTableName('project_positions');

        $query = <<<SQL
        INSERT INTO {$teamPos} ( `name` ) VALUES ( :name ) 
        SQL;

        $sth = $this->con->prepare($query);
        $status = $sth->execute([
            ':name' => $name
        ]);

        return ($status === true) ? $this->con->lastInsertId() : false;
    }

    public function getProjectPosition(int $projID, int $userTeamID)
    {
        $projPos = Connection::formatTableName('project_positions');
        $userProj = Connection::formatTableName('user_team_projects');
        
        
        $query = <<<SQL
        SELECT 
            proj_pos.name
        FROM {$this->table} as proj
            INNER JOIN {$userProj} as up
                ON up.project_id = proj.project_id
            INNER JOIN {$projPos} as proj_pos
                ON up.project_position_id = proj_pos.project_position_id
        WHERE proj.project_id = :projID AND up.user_team_id = :userTeamID
        SQL;

        $sth = $this->con->prepare($query);
        $status = $sth->execute([
            ':projID' => $projID,
            ':userTeamID' => $userTeamID
        ]);
        
        if ($status === true) 
            return $sth->fetch(PDO::FETCH_ASSOC);
        
        return false;
    }

    public function getMembersInitials(int $projID)
    {
        $users = Connection::formatTableName('users');
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


        $sth = $this->con->prepare($query);
        $status = $sth->execute([
            ':projID' => $projID
        ]);

        if ($status === true) 
            return $sth->fetchAll(PDO::FETCH_ASSOC);

        return false;
    }



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

        $sth = $this->con->prepare($query);
        $status = $sth->execute([
            ':teamID' => $teamID
        ]);

        if ($status === true) 
            return $sth->fetchAll(PDO::FETCH_ASSOC);

        return false;
    }
}


?>