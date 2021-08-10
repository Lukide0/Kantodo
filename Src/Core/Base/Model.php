<?php

namespace Kantodo\Core\Base;

use Kantodo\Core\Database\Connection;

/**
 * Základ modelu
 */
class Model
{
    /**
     * @var PDO
     */
    protected $con;

    /**
     * @var string
     */
    protected $table;

    public function __construct()
    {
        $this->con = Connection::getInstance();
    }

    /**
     * Získá data z tabulky
     *
     * @param   string  $formatedTableName  název tabulky
     * @param   array   $tableColumns       sloupce tabulky
     * @param   array   $select             sloupce, které chceceme vybrat ve formátu ['sloupec', 'sloupec'] nebo ['sloupec' => 'alias']
     * @param   array   $search             např. ['id' => 5]
     * @param   int     $limit              limit
     *
     * @return  array|false                 vrací false pokud se nepodařilo získat data z tabulky
     */
    protected function query(string $formatedTableName, array $tableColumns, array $select = ['*'], array $search = [], int $limit = 0)
    {

        if (count($select) == 0) {
            return [];
        }

        if (in_array('*', $select)) {
            $select = ['*'];
        } else {
            $columns = [];
            foreach ($select as $key => $name) {
                if (in_array($key, $tableColumns, true)) {
                    $columns[] = "$key as $name";
                } else if (in_array($name, $tableColumns)) {
                    $columns[] = $name;
                }
            }
        }

        if (count($columns) == 0) {
            return [];
        }

        $searchData = [];
        $queryData  = [];

        foreach ($tableColumns as $column) {
            if (isset($search[$column])) {
                $searchData[]          = "{$column} = :{$column}";
                $queryData[":$column"] = $search[$column];
            }
        }

        $query = 'SELECT ' . implode(', ', $columns) . " FROM {$formatedTableName}";
        if (count($search) != 0) {
            $query .= ' WHERE ' . implode(' AND ', $searchData);
        }

        if ($limit >= 1) {
            $query .= " LIMIT {$limit}";
        }

        $sth = $this->con->prepare($query);
        $sth->execute($queryData);
        $data = $sth->fetchAll(\PDO::FETCH_ASSOC);
        return $data;
    }
}
