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

    public function __construct(string $prefix = '') {
        $this->prefix = $prefix;
    }

    public function create(string $table, Closure $closure)
    {
        if ($this->hasTable($table))
            throw new SchemaException("Table `{$table}` already exists");

        $this->tables[$table] = $blueprint = new Blueprint($table, $this->prefix);

        call_user_func($closure, $blueprint, $this);

        try {
            $this->queries[] = $blueprint->createTableSQL();
        } catch (ColumnException $cE) {
            throw new ColumnException($cE->getMessage());
        }
    }
    
    
    public function modify(string $table, Closure $callback)
    {
        if (!$this->hasTable($table))
            throw new SchemaException("Table `{$table}` doesn't exists");

        $modified = clone $this->tables[$table];
        call_user_func($callback, $modified);


        $changes = self::compare($this->tables[$table], $modified);
        

        $this->tables[$table] = $modified;
        $this->queries[] = $changes->getChangesAsSQL();        
    }

    public static function compare(Blueprint $original, Blueprint $modified) 
    {
        return new TableChanges($original, $modified);
    }
    
    
    public function drop(string $table)
    {
        if (!$this->hasTable($table))
            throw new SchemaException("Table `{$table}` doesn't exists");

        $this->queries[] = "DROP TABLE IF EXISTS {$this->formatTableName($table)};";
        unset($this->tables[$table]);
    }
    
    
    public function rename(string $from, string $to)
    {
        if (!$this->hasTable($from))
            throw new SchemaException("Table `{$from}` doesn't exists");

        if ($this->hasTable($to))
            throw new SchemaException("Can't rename table from `{$from}` to `{$to}` because table `{$to}` exists");

        $this->queries[] = "RENAME TABLE {$this->formatTableName($from)} TO {$this->formatTableName($to)}";

        // rename key
        $this->tables[$to] = $this->tables[$from];
        unset($this->tables[$from]);

    }

    public function clearSQL()
    {
        $this->queries = [];
    }

    public function getQueries() 
    {
        return $this->queries;
    }

    public function getSQL()
    {
        return implode('\n\n',$this->queries);
    }

    public function hasTable(string $table)
    {
        return isset($this->tables[$table]);
    }

    public function getTable(string $table) 
    {
        return $this->tables[$table];
    }

    public function hasColumn(string $table, string $column)
    {
        return $this->hasTable($table) && $this->tables[$table]->columnExits($column);
    }

    public function getTablesNames() 
    {
        return array_keys($this->tables);
    }
    
    public function formatTableName(string $table) 
    {
        return $this->prefix . $table;
    }
}




?>