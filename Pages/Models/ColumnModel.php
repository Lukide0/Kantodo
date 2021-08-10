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
}
