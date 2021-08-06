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
        $userTeams = Connection::formatTableName('user_teams');
        $query = <<<SQL
        SELECT 
            t.name,
            t.team_id,
            COUNT(ut_count.user_team_id) as `members`
        FROM {$userTeams} as ut 
            INNER JOIN {$this->table} as t 
                ON t.team_id = ut.team_id
            LEFT JOIN {$userTeams} as ut_count 
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

        $tableColumns = ['team_id', 'name', 'uuid', 'description', 'password', 'is_public'];

        return $this->query($this->table, $tableColumns, $columns, $search, $limit);
    }


    public function getInfo(int $teamID)
    {
        $userTeams = Connection::formatTableName('user_teams');
        $projects = Connection::formatTableName("projects");

        $query = <<<SQL
        SELECT 
            t.name,
            t.description,
            COUNT(ut_count.user_team_id) as `members`,
            COUNT(proj.project_id) as `projects`
        FROM {$this->table} as t
            LEFT JOIN {$userTeams} as ut_count 
                ON ut_count.team_id = t.team_id
            LEFT JOIN {$projects} as proj
                ON proj.team_id = t.team_id
        WHERE t.team_id = :teamID
        SQL;

        $sth = $this->con->prepare($query);
        $status = $sth->execute([
            ":teamID" => $teamID
        ]);

        if ($status === true) 
            return $sth->fetch(PDO::FETCH_ASSOC);

        return false;
    }

    public function create(string $name, int $userID ,string $desc = '', bool $public = false)
    {
        $uuid = Generator::uuidV4();
        
        $sth = $this->con->prepare("INSERT INTO {$this->table} ( `name`, `description`, `is_public`, `uuid`) VALUES ( :name, :desc, :is_public, :uuid)");
        $status = $sth->execute([
            ':name' => $name,
            ':desc' => $desc,
            ':is_public' => $public,
            ':uuid' => $uuid
        ]);

        if ($status == true)
        {
            $teamID = $this->con->lastInsertId();
            $posID  = $this->getPosition('admin');

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

            mkdir(Application::$DATA_PATH . $uuid);

            return $teamID;
        }

        return false;
    }

    public function delete(int $teamID)
    {
        $dir = $this->get(['uuid'], ['team_id' => $teamID], 1);

        if ($dir === false || count($dir) == 0)
            return false;

        $dir = $dir[0]['uuid'];


        if (!rmdir(Application::$DATA_PATH . $dir)) 
            return false;
        
        $sth = $this->con->prepare("DELETE FROM {$this->table} WHERE team_id = :teamID");
        $status = $sth->execute([
            ":teamID" => $teamID
        ]);

        return $status;
    }

    
    
    public function getTeamPosition(int $teamID, int $userID)
    {
        $teamPos = Connection::formatTableName('team_positions');
        $userTeams = Connection::formatTableName('user_teams');
        
        
        $query = <<<SQL
        SELECT 
            tp.name,
            ut.user_team_id as `id`
        FROM {$this->table} as t
            INNER JOIN {$userTeams} as ut
                ON ut.team_id = t.team_id
            INNER JOIN {$teamPos} as tp
                ON ut.team_position_id = tp.team_position_id
        WHERE t.team_id = :teamID AND ut.user_id = :userID
        SQL;
        $sth = $this->con->prepare($query);
        $status = $sth->execute([
            ':teamID' => $teamID,
            ':userID' => $userID
        ]);
        
        if ($status === true) 
            return $sth->fetch(PDO::FETCH_ASSOC);
        
        return false;
        
    }

    public function setUserPosition(int $userID, int $teamID, int $posID)
    {
        $userTeams = Connection::formatTableName('user_teams');
        $sth = $this->con->prepare("INSERT INTO {$userTeams} (`user_id`, `team_id`, `team_position_id`) VALUES ( :userID, :teamID, :posID)");
        $status = $sth->execute([
            ':userID' => $userID,
            ':teamID' => $teamID,
            ':posID' => $posID
        ]);

        return ($status === true) ? $this->con->lastInsertId() : false; 
    }
    
    public function getPosition(string $name)
    {
        $teamPos = Connection::formatTableName('team_positions');

        $sth = $this->con->prepare("SELECT `team_position_id` FROM {$teamPos} WHERE `name` = :name LIMIT 1");
        
        $status = $sth->execute([
            ':name' => $name
        ]);

        if ($status === true) 
        {
            $result = $sth->fetch(PDO::FETCH_ASSOC);
            if (count($result) != 0)
                return $result['team_position_id'];
            return false;
        }

        return false;
    }
    
    public function createPosition(string $name)
    {
        $teamPos = Connection::formatTableName('team_positions');

        $query = <<<SQL
        INSERT INTO {$teamPos} ( `name` ) VALUES ( :name ) 
        SQL;

        $sth = $this->con->prepare($query);
        $status = $sth->execute([
            ':name' => $name
        ]);

        return ($status === true) ? $this->con->lastInsertId() : false;
    }
}


?>