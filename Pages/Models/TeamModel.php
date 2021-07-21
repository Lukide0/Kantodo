<?php 

namespace Kantodo\Models;

use Kantodo\Core\Application;
use Kantodo\Core\Database\Connection;
use Kantodo\Core\Generator;
use Kantodo\Core\Model;
use PDO;

class TeamModel extends Model
{
    public function __construct() {
        parent::__construct();
        $this->table = Connection::formatTableName('teams');
    }

    public function getUserTeams()
    {
        //$user_teams = Connection::formatTableName('user_teams');
        // TODO

        //$this->con->prepare("SELECT t.`team_id`, t.`name` FROM {$this->table} as t INNER JOIN {$user_teams} as ut ON ut.team_id = t.team_id WHERE ut.user_id = :user_id")
        return [];
    }

    public function create(string $name, string $desc = '', bool $public = false)
    {
        $folder = Generator::uuidV4();
        
        $sth = $this->con->prepare("INSERT INTO {$this->table} ( `name`, `description`, `dir_name`, `is_public`) VALUES ( :name, :desc, :dir_name, :is_public)");
        $status = $sth->execute([
            ':name' => $name,
            ':desc' => $desc,
            ':is_public' => $public,
            ':dir_name' =>  $folder
        ]);

        if ($status == true) 
        {
            $status = mkdir(Application::$DATA_PATH . $folder);
            return ($status === true) ? $this->con->lastInsertId() : false;
        }

        return false;
    }

    public function setUserPosition(int $userID, int $teamID, int $posID)
    {
        $userTeam = Connection::formatTableName('user_teams');
        $sth = $this->con->prepare("INSERT INTO {$userTeam} (`user_id`, `team_id`, `position_id`) VALUES ( :userID, :teamID, :posID)");
        $status = $sth->execute([
            ':userID' => $userID,
            ':teamID' => $teamID,
            ':posID' => $posID
        ]);

        return ($status === true) ? $this->con->lastInsertId() : false; 
    }

    public function getPosition(string $name)
    {
        $positions = Connection::formatTableName('positions');
        $sth = $this->con->prepare("SELECT * FROM {$positions} WHERE `name` = :name LIMIT 1");
        
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

    }
}


?>