<?php

namespace Kantodo\Core;

use Kantodo\Core\Database\Connection;

abstract class Model
{
    protected $con;
    protected $table;
    private function __clone() { }
    
    public function __construct() {
        $this->con = Connection::getInstance();
    }

    protected function query(string $formatedTableName, array $tableColumns, array $columns = ['*'], array $search = [], int $limit = 0)
    {
        if (count($columns) == 0) 
            return [];
    
        if (in_array('*', $columns)) 
        {
            $columns = ['*'];
        } else 
        {
            $columns = array_intersect($tableColumns, $columns);
        }

        if (count($columns) == 0) 
            return [];

        $searchData = [];
        $queryData = [];

        foreach ($tableColumns as $column) {
            if (isset($search[$column])) 
            {
                $searchData[] = "{$column} = :{$column}";
                $queryData[":$column"] = $search[$column];
            }
        }

        $query = 'SELECT ' . implode(', ', $columns) . " FROM {$formatedTableName}";
        if (count($search) != 0) 
            $query .= ' WHERE ' . implode(' AND ', $searchData);
        
        if ($limit >= 1)
            $query .= " LIMIT {$limit}";

        $sth = $this->con->prepare($query);
        $sth->execute($queryData);
        $data = $sth->fetchAll(\PDO::FETCH_ASSOC);
        return $data;
    }
}



?>