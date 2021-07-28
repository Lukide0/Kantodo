<?php 

namespace Kantodo\Models;

use Kantodo\Core\Database\Connection;
use Kantodo\Core\Model;
use PDO;

class ProjectModel extends Model
{
    public function __construct() {
        parent::__construct();
        $this->table = Connection::formatTableName('projects');
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
            ":teamID" => $teamID
        ]);

        if ($status === true) 
            return $sth->fetchAll(PDO::FETCH_ASSOC);

        return false;
    }
}


?>