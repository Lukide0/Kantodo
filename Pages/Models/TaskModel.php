<?php

declare(strict_types = 1);

namespace Kantodo\Models;

use DateTime;
use Kantodo\Core\Base\Model;
use Kantodo\Core\Database\Connection;

/**
 * Model na úkoly
 */
class TaskModel extends Model
{
    public function __construct()
    {
        parent::__construct();
        $this->table = Connection::formatTableName('tasks');

        $this->setColumns([
            'task_id',
            'name',
            'description',
            'priority',
            'completed',
            'end_date',
            'creator_id',
            'project_id',
        ]);
    }

    /**
     * Vytvoří úkol
     *
     * @param   string  $name         jméno úkolu
     * @param   int     $creatorID    id tvůrce
     * @param   int     $projectID    id projektu
     * @param   string  $desc         popis
     * @param   int     $priority     priorita (0-255)
     * @param   DateTime  $endDate      datum dokončení
     * @param   bool    $completed    je úkol dokončen
     *
     * @return  int|false             vrací id záznamu nebo false pokud se nepovedlo vložit do databáze
     */
    public function create(string $name, int $creatorID, int $projectID, string $desc = null, int $priority = 1, DateTime $endDate = null, bool $completed = false)
    {
        if ($endDate !== null) {
            $endDate = date(Connection::DATABASE_DATE_FORMAT, $endDate->getTimestamp());
        }

        $query = <<<SQL
        INSERT INTO {$this->table} (
            `name`,
            `description`,
            `priority`,
            `end_date`,
            `creator_id`,
            `project_id`,
            `completed`
        )
        VALUES(
            :name,
            :desc,
            :priority,
            :endDate,
            :creatorID,
            :projID,
            :completed
        )
        SQL;

        $sth    = $this->con->prepare($query);
        $status = $sth->execute([
            ':name'        => $name,
            ':desc'        => $desc,
            ':priority'    => $priority,
            ':endDate'     => $endDate,
            ':creatorID'   => $creatorID,
            ':projID'      => $projectID,
            ':completed'   => (int)$completed,
        ]);

        if ($status === true) {
            return (int)$this->con->lastInsertId();
        }

        return false;
    }
    
    /**
     * Upraví úkol
     *
     * @param   int    $taskID   id úkolu
     * @param   array<string,mixed>  $columns  sloupce s novou hodnotou
     *
     * @return  bool           status
     */
    public function update(int $taskID, array $columns)
    {
        $columnsTable = $this->getTableColumns();

        $query = "UPDATE {$this->table} SET";
        $data = [];

        foreach ($columns as $key => $value) {
            if (!in_array($key, $columnsTable, true)) 
                continue;
            
            $query .= " {$key} = :{$key},";
            $data[":{$key}"] = $value;
        }
        
        if (count($data) == 0)
            return false;

        $query[strlen($query) - 1] = ' ';

        $query .= "WHERE task_id = :taskID";
        $data[':taskID'] = $taskID;

        $sth = $this->con->prepare($query);
        $status   = $sth->execute($data);
        return $status;
    }

    /**
     * Smaže úkol
     *
     * @param   int  $taskID    id úkolu
     *
     * @return  bool            status
     */
    public function delete(int $taskID)
    {
        $sth    = $this->con->prepare("DELETE FROM {$this->table} WHERE task_id = :taskID");
        $status = $sth->execute([
            ":taskID" => $taskID,
        ]);

        return $status;
    }
}
