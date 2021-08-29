<?php

namespace Kantodo\Models;

use Kantodo\Core\Base\Model;
use Kantodo\Core\Database\Connection;

/**
 * Model na sloupce
 */
class ColumnModel extends Model
{
    public function __construct()
    {
        parent::__construct();
        $this->table = Connection::formatTableName('columns');

        $this->setColumns([
            'column_id',
            'name',
            'max_task_count',
            'project_id',
        ]);
    }

    /**
     * Vytvoří sloupec
     *
     * @param   string  $name          název sloupce
     * @param   int     $projID        id projektu
     * @param   int     $maxTaskCount  max. počet úkolů ve sloupci
     *
     * @return  false|int              vrací id záznamu nebo false pokud se nepovedlo vložit do databáze
     */
    public function create(string $name, int $projID, int $maxTaskCount = null)
    {
        $query = <<<SQL
        INSERT INTO {$this->table} (
            `name`,
            `max_task_count`,
            `project_id`
        )
        VALUES(:name, :maxTaskCount, :projID)
        SQL;

        $sth    = $this->con->prepare($query);
        $status = $sth->execute([
            ':name'         => $name,
            ':maxTaskCount' => $maxTaskCount,
            ':projID'       => $projID,
        ]);

        if ($status) {
            return $this->con->lastInsertId();
        }

        return false;
    }

    public function getCountOfTasks(int $columnID)
    {
        $tasks = Connection::formatTableName('tasks');

        $query = <<<SQL
        SELECT
            COUNT(`task_id`) as `tasks_count`
        FROM {$tasks}
        WHERE column_id = :columnID
        LIMIT 1
        SQL;

        $sth    = $this->con->prepare($query);
        $status = $sth->execute([
            'columnID' => $columnID,
        ]);

        if ($status) {
            $row = $sth->fetch(\PDO::FETCH_ASSOC);

            if (count($row) === 0) {
                return false;
            }

            return $row['tasks_count'];
        }

        return false;

    }
}
