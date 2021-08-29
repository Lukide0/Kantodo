<?php

class DB
{
    private $tables = array();
    private $prefix = "";
    private $conn;
    private $lastWhere;
    private $lastInsertedId;

    public function __construct()
    {}

    public function Select(string $table, array $columns = [], string $where = "", string $out = "KEY_VALUE")
    {
        switch ($out) {
            case 'NUM_VALUE':
                $out = PDO::FETCH_NUM;
                break;
            case 'OBJ':
                $out = PDO::FETCH_OBJ;
                break;
            default:
                $out = PDO::FETCH_ASSOC;
                break;
        }

        $columnsString = (count($columns) > 0) ? implode(", ", $columns) : "* ";
        $query         = "SELECT " . $columnsString . "FROM " . $this->prefix . $table;

        if (strlen($where) > 0) {
            $query .= " WHERE " . $where;
        }
    }
    public function Insert()
    {}

    public function Update()
    {}

    public function Delete()
    {}
}
