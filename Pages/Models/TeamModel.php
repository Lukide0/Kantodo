<?php 

namespace Kantodo\Models;

use Kantodo\Core\Application;
use Kantodo\Core\Database\Connection;
use Kantodo\Core\Generator;
use Kantodo\Core\Model;
use PDO;

class TeamModel extends Model
{
    const POSITIONS = [
        'admin' => [
            'editTeamSettings' => true,
            'addProject' => true,
            'removeProject' => true,
            'addPeople' => true,
            'removePeople' => true,
            'changePeoplePosition' => true
        ],
        'team_manager' => [
            'editTeamSettings' => true,
            'addProject' => false,
            'removeProject' => false,
            'addPeople' => true,
            'removePeople' => true,
            'changePeoplePosition' => true
            ],
        'project_manager' => [
            'editTeamSettings' => false,
            'addProject' => true,
            'removeProject' => true,
            'addPeople' => false,
            'removePeople' => false,
            'changePeoplePosition' => false
        ],
        'recruiter' => [
            'editTeamSettings' => false,
            'addProject' => false,
            'removeProject' => false,
            'addPeople' => true,
            'removePeople' => true,
            'changePeoplePosition' => false
        ],
        'user' => [
            'editTeamSettings' => false,
            'addProject' => false,
            'removeProject' => false,
            'addPeople' => false,
            'removePeople' => false,
            'changePeoplePosition' => false
        ]
    ];


    public function __construct() {
        parent::__construct();
        $this->table = Connection::formatTableName('teams');
    }

    public function getUserTeams(int $userID)
    {
        $user_teams = Connection::formatTableName('user_teams');
        $query = <<<SQL
        SELECT 
            t.name,
            t.uuid,
            COUNT(ut_count.user_team_id) as `members`
        FROM `todo_user_teams` as ut 
            INNER JOIN `todo_teams` as t 
                ON t.team_id = ut.team_id
            LEFT JOIN `todo_user_teams` as ut_count 
                ON ut_count.team_id = ut.team_id 
        WHERE ut.user_id = :userID 
        GROUP BY t.name;
        SQL;
        
        $sth = $this->con->prepare($query);
        $status = $sth->execute([
            ":userID" => $userID
        ]);

        if ($status === true) 
            return $sth->fetchAll(PDO::FETCH_ASSOC);

        return false;
    }

    public function get(array $columns = ['*'], array $search = [], int $limit = 0) 
    {
        if (count($columns) == 0) 
            return [];

        $tableColumns = ['team_id', 'name', 'dir_name', 'description', 'password', 'is_public'];

        return $this->query($this->table, $tableColumns, $columns, $search, $limit);
    }

    public function create(string $name, int $userID ,string $desc = '', bool $public = false)
    {
        $folder = Generator::uuidV4();
        $uuid = Generator::uuidV4();
        
        $sth = $this->con->prepare("INSERT INTO {$this->table} ( `name`, `description`, `dir_name`, `is_public`, `uuid`) VALUES ( :name, :desc, :dir_name, :is_public, :uuid)");
        $status = $sth->execute([
            ':name' => $name,
            ':desc' => $desc,
            ':is_public' => $public,
            ':dir_name' =>  $folder,
            ':uuid' => $uuid
        ]);

        if ($status == true)
        {
            $teamID = $this->con->lastInsertId();
            $posID  = $this->getPosition("admin", true);

            if ($posID === false) 
            {
                $this->delete($teamID);
                return false;
            }
            $posID = (int) $posID['team_position_id'];

            $status = $this->setUserPosition($userID, $teamID, $posID);

            if ($status === false) 
            {
                $this->delete($teamID);
                return false;
            }

            mkdir(Application::$DATA_PATH . $folder);

            return $this->con->lastInsertId();
        }

        return false;
    }

    public function delete(int $teamID)
    {
        $dir = $this->get(['dir_name'], ['team_id' => $teamID], 1);

        if ($dir === false || count($dir) == 0)
            return false;

        $dir = $dir[0]['dir_name'];


        if (!rmdir(Application::$DATA_PATH . $dir)) 
            return false;
        
        $sth = $this->con->prepare("DELETE FROM {$this->table} WHERE team_id = :teamID");
        $status = $sth->execute([
            ":teamID" => $teamID
        ]);

        return $status;
    }

    public function setUserPosition(int $userID, int $teamID, int $posID)
    {
        $userTeam = Connection::formatTableName('user_teams');
        $sth = $this->con->prepare("INSERT INTO {$userTeam} (`user_id`, `team_id`, `team_position_id`) VALUES ( :userID, :teamID, :posID)");
        $status = $sth->execute([
            ':userID' => $userID,
            ':teamID' => $teamID,
            ':posID' => $posID
        ]);

        return ($status === true) ? $this->con->lastInsertId() : false; 
    }

    public function getPosition(string $name, bool $onlyID = false)
    {
        $teamPos = Connection::formatTableName('team_positions');

        if ($onlyID)
            $sth = $this->con->prepare("SELECT `team_position_id` FROM {$teamPos} WHERE `name` = :name LIMIT 1");
        else
            $sth = $this->con->prepare("SELECT * FROM {$teamPos} WHERE `name` = :name LIMIT 1");
        
        $status = $sth->execute([
            ':name' => $name
        ]);


        if ($status === true) 
            return $sth->fetch(PDO::FETCH_ASSOC);

        return false;
    }

    public function createPosition(string $name, array $privileges)
    {
        $canEditTeamSettings    = $privileges['editTeamSettings'] ?? false;
        $canAddProject          = $privileges['addProject'] ?? false;
        $canRemoveProject       = $privileges['removeProject'] ?? false;
        $canAddPeople           = $privileges['addPeople'] ?? false;
        $canRemovePeople        = $privileges['removePeople'] ?? false;
        $canChangePeoplePosition= $privileges['changePeoplePosition'] ?? false;

        $teamPos = Connection::formatTableName("team_positions");

        $query = <<<SQL
        INSERT INTO {$teamPos}
                    (
                    `name` ,
                    `can_edit_team_settings` ,
                    `can_add_project` ,
                    `can_remove_project` ,
                    `can_add_people` ,
                    `can_remove_people` ,
                    `can_change_people_position` )
        VALUES      (
                    :name,
                    :can_edit_team_setting,
                    :can_add_project,
                    :can_remove_project,
                    :can_add_people,
                    :can_remove_people,
                    :can_change_people_position
                    ) 
        SQL;

        $sth = $this->con->prepare($query);
        $status = $sth->execute([
            ":name" => $name,
            ":can_edit_team_setting" => $canEditTeamSettings,
            ":can_add_project" => $canAddProject,
            ":can_remove_project" => $canRemoveProject,
            ":can_add_people" => $canAddPeople,
            ":can_remove_people" => $canRemovePeople,
            ":can_change_people_position" => $canChangePeoplePosition
        ]);

        return ($status === true) ? $this->con->lastInsertId() : false;
    }
}


?>