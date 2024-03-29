<?php

declare(strict_types=1);

namespace Kantodo\Models;

use Kantodo\Core\Database\Connection;
use Kantodo\Core\Base\Model;
use PDO;

/**
 * Model na štítky
 */
class TagModel extends Model
{
    public function __construct()
    {
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
     * @return  int|false           id štítku nebo false
     */
    public function createInProject(string $name, int $projectID)
    {
        $tagID = $this->getSingle(['tag_id'], ['name' => $name]);

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
            return $tagID;


        $query = "INSERT INTO {$tagProject} (`tag_id`, `project_id`) VALUES(:tagID, :projectID)";
        $sth    = $this->con->prepare($query);
        $status = $sth->execute([
            ':tagID' => $tagID,
            ':projectID' => $projectID
        ]);

        if ($status === true) {
            return $tagID;
        }

        return false;
    }

    /**
     * Přidá štítky úkolu
     *
     * @param   array<int,int>  $tags  id štítků
     * @param   int  $taskID  id úkolu
     *
     * @return  bool                   status
     */
    public function addTagsToTask(array $tags, int $taskID)
    {
        $queries = [];
        $taskTag = Connection::formatTableName('task_tags');

        foreach ($tags as $tagID) {
            $queries[] = "INSERT INTO {$taskTag} (`tag_id`, `task_id`) VALUES ({$tagID}, {$taskID})";
        }

        return Connection::runInTransaction($queries);
    }

    /**
     * Nastaví štítky úkolu
     *
     * @param   array<string>  $tags  štítky
     * @param   int  $taskID  id úkolu
     * @param   int  $projectID  id projektu
     *
     * @return  bool                   status
     */
    public function setTagsToTask(array $tags, int $taskID, int $projectID)
    {

        $tagsID = [];
        foreach ($tags as $tag) {
            $tagID = $this->createInProject($tag, $projectID);

            if ($tagID == false)
                return false;
            $tagsID[] = $tagID;
        }

        $queries = [];
        $taskTag = Connection::formatTableName('task_tags');

        // smaže všechy štítky a poté nastaví pouze ty, které mají být
        $queries[] = "DELETE FROM {$taskTag} WHERE task_id = {$taskID}";

        foreach ($tagsID as $tagID) {
            $queries[] = "INSERT INTO {$taskTag} (`tag_id`, `task_id`) VALUES (\"{$tagID}\", {$taskID})";
        }

        return Connection::runInTransaction($queries);
    }

    /**
     * Získá štítky úkolu
     *
     * @param   int  $taskID  id úkolu
     *
     * @return  array<string>|false  štítky
     */
    public function getTaskTags(int $taskID)
    {
        $taskTags = Connection::formatTableName('task_tags');

        $query = "SELECT tag.name FROM {$taskTags} as tt INNER JOIN {$this->table} as tag ON tag.tag_id = tt.tag_id WHERE tt.task_id = :taskID";
        $sth    = $this->con->prepare($query);
        $status = $sth->execute([
            ':taskID' => $taskID,
        ]);

        if ($status === true) {
            $result = $sth->fetchAll(PDO::FETCH_COLUMN);
            if ($result !== false) {
                return $result;
            }
            return false;
        }

        return false;
    }
}
