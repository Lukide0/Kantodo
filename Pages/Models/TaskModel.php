<?php

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
            'index',
            'creator_id',
            'milestone_id',
            'column_id'
        ]);
    }

    /**
     * Vytvoří úkol
     *
     * @param   string  $name         jméno úkolu
     * @param   string  $index        index (lexorank)
     * @param   int     $creatorID    id tvůrce
     * @param   int     $columnID     id sloupce
     * @param   string  $desc         popis
     * @param   int     $priority     priorita (0-255)
     * @param   string  $endDate      datum dokončení
     * @param   int     $milestoneID  id milestone
     *
     * @return  int|false             vrací id záznamu nebo false pokud se nepovedlo vložit do databáze
     */
    public function create(string $name, string $index, int $creatorID, int $columnID, string $desc = null, int $priority = 1, string $endDate = null, int $milestoneID = null)
    {
        if ($endDate !== null) {
            $endDate = date(Connection::DATABASE_DATE_FORMAT, strtotime($endDate));
        }

        $query = <<<SQL
        INSERT INTO {$this->table} (
            `name`,
            `description`,
            `priority`,
            `end_date`,
            `index`,
            `creator_id`,
            `milestone_id`,
            `column_id`
        )
        VALUES(
            :name,
            :desc,
            :priority,
            :endDate,
            :index,
            :creatorID,
            :milestoneID,
            :columnID
        )
        SQL;

        $sth    = $this->con->prepare($query);
        $status = $sth->execute([
            ':name'        => $name,
            ':desc'        => $desc,
            ':priority'    => $priority,
            ':endDate'     => $endDate,
            ':index'       => $index,
            ':creatorID'   => $creatorID,
            ':milestoneID' => $milestoneID,
            ':columnID'    => $columnID,
        ]);

        if ($status === true) {
            return $this->con->lastInsertId();
        }

        return false;
    }
}
