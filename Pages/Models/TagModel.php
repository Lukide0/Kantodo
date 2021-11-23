<?php 

declare(strict_types = 1);

namespace Kantodo\Models;

use Kantodo\Core\Database\Connection;
use Kantodo\Core\Base\Model;
use PDO;

class TagModel extends Model
{
    public function __construct() {
        parent::__construct();
        $this->table = Connection::formatTableName('tags');

        $this->setColumns([
            'tag_id',
            'name'
        ]);
    }

    /**
     * Vytvoří štítek
     *
     * @param   string  $name         jméno štítku
     *
     * @return  int|false             vrací id záznamu nebo false pokud se nepovedlo vložit do databáze
     */
    public function create(string $name)
    {
        $query = "INSERT INTO {$this->table} (`name`) VALUES(:name)";
        $sth    = $this->con->prepare($query);
        $status = $sth->execute([
            ':name' => $name,
        ]);

        if ($status === true) {
            return (int)$this->con->lastInsertId();
        }

        return false;
    }

    /**
     * Přidá tag do týmu, pokud neexistuje tak ho vytvoří
     *
     * @param   string  $name       název štítku
     * @param   int     $projectID  projekt id
     *
     * @return  bool                status
     */
    public function createInProject(string $name, int $projectID)
    {
        $tagID = $this->getSingle(['tag_id'],['name' => $name]);

        if ($tagID === false)
            $tagID = $this->create($name);
        else 
            $tagID = (int)$tagID['tag_id'];

        
        if (!is_int($tagID))
            return false;

            
        $tagProject = Connection::formatTableName('tag_projects');

        $exists = "SELECT tag_id FROM {$tagProject} WHERE tag_id = :tagID AND project_id = :projectID LIMIT 1";
        $sth = $this->con->prepare($exists);
        $status = $status = $sth->execute([
            ':tagID' => $tagID,
            ':projectID' => $projectID
        ]);

        $result = $sth->fetch(PDO::FETCH_ASSOC);
        if ($status && $result !== false)
            return true;

            
        $query = "INSERT INTO {$tagProject} (`tag_id`, `project_id`) VALUES(:tagID, :projectID)";
        $sth    = $this->con->prepare($query);
        $status = $sth->execute([
            ':tagID' => $tagID,
            ':projectID' => $projectID
        ]);

        if ($status === true) {
            return true;
        }

        return false;
    }
}


?>