<?php

namespace Kantodo\Core\Base;

use Kantodo\Core\Database\Connection;

/**
 * Základ modelu
 */
class Model
{
    /**
     * @var \PDO
     */
    protected $con;

    /**
     * @var string
     */
    protected $table;

    /**
     * sloupce tabulky
     *
     * @var array<string>
     */
    private $tableColumns = [];

    public function __construct()
    {
        $this->con = Connection::getInstance();
    }

    /**
     * Získá data z tabulky
     *
     * @param   array<string>|array<string,string>   $select             sloupce, které chceceme vybrat ve formátu ['sloupec', 'sloupec'] nebo ['sloupec' => 'alias']
     * @param   array<string,mixed>   $search             např. ['id' => 5]
     * @param   int     $limit              limit
     * @param   int     $offset             offset
     * 
     * @return  array<mixed>|false                 vrací false pokud se nepodařilo získat data z tabulky
     */
    public function get(array $select = ['*'], array $search = [], int $limit = 0, int $offset = 0)
    {        
        if (count($select) == 0) {
            return [];
        }

        if (in_array('*', $select)) {
            $columns = ['*'];
        } else {
            $columns = [];
            foreach ($select as $key => $name) {
                if (in_array($key, $this->tableColumns, true)) {
                    $columns[] = "`$key` as '$name'";
                } else if (in_array($name, $this->tableColumns)) {
                    $columns[] = "`$name`";
                }
            }
        }

        if (count($columns) == 0) {
            return [];
        }

        $searchData = [];
        $queryData  = [];

        foreach ($this->tableColumns as $column) {
            if (isset($search[$column])) {
                $searchData[]          = "{$column} = :{$column}";
                $queryData[":$column"] = $search[$column];
            }
        }

        $query = 'SELECT ' . implode(', ', $columns) . " FROM {$this->table}";
        if (count($searchData) != 0) {
            $query .= ' WHERE ' . implode(' AND ', $searchData);
        }

        if ($limit >= 1 && $offset >= 1) {
            $query .= " LIMIT {$offset},{$limit}";
        } 
        else if ($offset >= 1) 
        {
            $query .= " OFFSET {$offset}";
        } 
        else if ($limit >= 1) 
        {
            $query .= " LIMIT {$limit}";
        }
        $sth = $this->con->prepare($query);
        $sth->execute($queryData);
        $data = $sth->fetchAll(\PDO::FETCH_ASSOC);
        return $data;
    }

    /**
     * Získá data z tabulky
     *
     * @param   array<string>|array<string,string>   $select             sloupce, které chceceme vybrat ve formátu ['sloupec', 'sloupec'] nebo ['sloupec' => 'alias']
     * @param   array<string,mixed>   $search             ['sloupec' => hodnota]
     *
     * @return  array<mixed>|false                 vrací false pokud se nepodařilo získat data z tabulky
     */
    public function getSingle(array $select = ['*'], array $search = [])
    {
        $data = $this->get($select, $search, 1);

        if ($data == false || count($data) != 1) {
            return false;
        }

        return $data[0];
    }

    /**
     * Nastavý sloupce tabulky
     *
     * @param   array<string>  $columns  sloupce
     *
     * @return void
     */
    protected function setColumns(array $columns)
    {
        $this->tableColumns = $columns;
    }

    /**
     * Sloupce tabulky
     *
     * @return  array<string> sloupce
     */
    public function getTableColumns()
    {
        return $this->tableColumns;
    }
}
