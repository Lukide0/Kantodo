<?php

declare(strict_types = 1);

namespace Kantodo\Models;

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
            'milestone_id',
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
     * @param   string  $endDate      datum dokončení
     * @param   int     $milestoneID  id milestone
     *
     * @return  int|false             vrací id záznamu nebo false pokud se nepovedlo vložit do databáze
     */
    public function create(string $name, int $creatorID, int $projectID, string $desc = null, int $priority = 1, string $endDate = null, int $milestoneID = null)
    {
        if ($endDate !== null) {
            $endDate = date(Connection::DATABASE_DATE_FORMAT, (int)strtotime($endDate));
        }

        $query = <<<SQL
        INSERT INTO {$this->table} (
            `name`,
            `description`,
            `priority`,
            `end_date`,
            `creator_id`,
            `milestone_id`,
            `project_id`,
            `completed`
        )
        VALUES(
            :name,
            :desc,
            :priority,
            :endDate,
            :creatorID,
            :milestoneID,
            :projID,
            0
        )
        SQL;

        $sth    = $this->con->prepare($query);
        $status = $sth->execute([
            ':name'        => $name,
            ':desc'        => $desc,
            ':priority'    => $priority,
            ':endDate'     => $endDate,
            ':creatorID'   => $creatorID,
            ':milestoneID' => $milestoneID,
            ':projID'      => $projectID,
        ]);

        if ($status === true) {
            return (int)$this->con->lastInsertId();
        }

        return false;
    }
}
