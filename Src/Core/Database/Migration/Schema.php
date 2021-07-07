<?php

namespace Kantodo\Core\Database\Migration;

use Closure;
use Kantodo\Core\Database\Migration\Blueprint;
use Kantodo\Core\Database\Connection;
use Kantodo\Core\Database\Migration\Exception\ColumnException;
use Kantodo\Core\Database\Migration\Exception\SchemaException;

class Schema
{
    /**
     * @var array<string,Blueprint>
     */
    private $tables = [];
    private $queries = [];
    private $prefix;

    public function __construct(string $prefix = "") {
        $this->prefix = $prefix;
    }

    public function Create(string $table, Closure $closure)
    {
        if ($this->HasTable($table))
            throw new SchemaException("Table `{$table}` already exists");

        $this->tables[$table] = $blueprint = new Blueprint($table, $this->prefix);

        call_user_func($closure, $blueprint);

        try {
            $this->queries[] = $blueprint->CreateTableSQL();
        } catch (ColumnException $cE) {
            throw new ColumnException($cE->getMessage());
        }
    }
    
    
    public function Modify(string $table, Closure $callback)
    {
        if (!$this->HasTable($table))
            throw new SchemaException("Table `{$table}` doesn't exists");

        $modified = clone $this->tables[$table];
        call_user_func($callback, $modified);


        $changes = self::Compare($this->tables[$table], $modified);
        

        $this->tables[$table] = $modified;
        $this->queries[] = $changes->GetChangesAsSQL();        
    }

    public static function Compare(Blueprint $original, Blueprint $modified) 
    {
        return new TableChanges($original, $modified);
    }
    
    
    public function Drop(string $table)
    {
        if (!$this->HasTable($table))
            throw new SchemaException("Table `{$table}` doesn't exists");
        
        $this->queries[] = "DROP TABLE {$this->FormatTableName($table)};";
        unset($this->tables[$table]);
    }
    
    
    public function Rename(string $from, string $to)
    {
        if (!$this->HasTable($from))
            throw new SchemaException("Table `{$from}` doesn't exists");

        if ($this->HasTable($to))
            throw new SchemaException("Can't rename table from `{$from}` to `{$to}` because table `{$to}` exists");

        $this->queries[] = "RENAME TABLE {$this->FormatTableName($from)} TO {$this->FormatTableName($to)}";

        // rename key
        $this->tables[$to] = $this->tables[$from];
        unset($this->tables[$from]);

    }

    public function GetQueries() 
    {
        return $this->queries;
    }

    public function GetSQL()
    {
        return implode("\n",$this->queries);
    }

    public function HasTable(string $table)
    {
        return isset($this->tables[$table]);
    }

    public function HasColumn(string $table, string $column)
    {
        return $this->HasTable($table) && $this->tables[$table]->ColumnExits($column);
    }
    
    public function FormatTableName(string $table) 
    {
        return $this->prefix . $table;
    }
}




?>