<?php

namespace Kantodo\Core\Database\Migration;

use Closure;
use Kantodo\Core\Database\Migration\Blueprint;
use Kantodo\Core\Database\Migration\Exception\SchemaException;
use Kantodo\Core\Database\Migration\Exception\TableException;

/**
 * Db schéma
 */
class Schema
{
    /**
     * @var array<Blueprint>
     */
    private $tables = [];

    /**
     * @var array<string>
     */
    private $queries = [];

    /**
     * @var string
     */
    private $prefix;

    public function __construct(string $prefix = '')
    {
        $this->prefix = $prefix;
    }

    /**
     * Vytvoří tabulku
     *
     * @param   string   $table    název tabulky
     * @param   Closure  $closure  funkce ve které se definují její sloupce
     *
     * @return  void
     *
     * @throws SchemaException      pokud už tabulka existuje
     * @throws TableException      pokud je tabulka nevalidní
     */
    public function create(string $table, Closure $closure)
    {
        if ($this->hasTable($table)) {
            throw new SchemaException("Table `{$table}` already exists");
        }

        $this->tables[$table] = $blueprint = new Blueprint($table, $this->prefix);

        call_user_func($closure, $blueprint, $this);

        // změna lokality excelption
        try {
            $this->queries[] = $blueprint->createTableSQL();
        } catch (TableException $cE) {
            throw new TableException($cE->getMessage());
        }
    }

    /**
     * Modifikuje tabulku
     *
     * @param   string   $table    název tabulky
     * @param   callable  $callback  funkce ve které se tabulka Modifikuje
     *
     * @return  void
     *
     * @throws SchemaException      pokud už tabulka existuje
     */
    public function modify(string $table, $callback)
    {
        if (!$this->hasTable($table)) {
            throw new SchemaException("Table `{$table}` doesn't exists");
        }

        $modified = clone $this->tables[$table];
        call_user_func($callback, $modified);

        $changes = self::compare($this->tables[$table], $modified);

        $this->tables[$table] = $modified;
        $this->queries[]      = $changes->getChangesAsSQL();
    }
    /**
     * Porovná tabulky
     *
     * @param   Blueprint  $original  originální
     * @param   Blueprint  $modified  modifikovaná
     *
     * @return  TableChanges          změny
     */
    public static function compare(Blueprint $original, Blueprint $modified)
    {
        return new TableChanges($original, $modified);
    }

    /**
     * Odstraní tabulku
     *
     * @param   string  $table  tabulka
     *
     * @return  void
     *
     * @throws SchemaException      pokud už tabulka neexistuje
     */
    public function drop(string $table)
    {
        if (!$this->hasTable($table)) {
            throw new SchemaException("Table `{$table}` doesn't exists");
        }

        $this->queries[] = "DROP TABLE IF EXISTS {$this->formatTableName($table)};";
        unset($this->tables[$table]);
    }

    /** Přejmenuje tabulku
     *
     * @param   string  $from
     * @param   string  $to
     * 
     * @return  void
     *
     * @throws SchemaException      pokud už tabulka neexistuje
     */
    public function rename(string $from, string $to)
    {
        if (!$this->hasTable($from)) {
            throw new SchemaException("Table `{$from}` doesn't exists");
        }

        if ($this->hasTable($to)) {
            throw new SchemaException("Can't rename table from `{$from}` to `{$to}` because table `{$to}` exists");
        }

        $this->queries[] = "RENAME TABLE {$this->formatTableName($from)} TO {$this->formatTableName($to)}";

        // rename key
        $this->tables[$to] = $this->tables[$from];
        unset($this->tables[$from]);
    }

    /**
     * Smaže SQL
     *
     * @return  void
     */
    public function clearSQL()
    {
        $this->queries = [];
    }

    /**
     * Získá SQL queries
     *
     * @return  array<string>
     */
    public function getQueries()
    {
        return $this->queries;
    }

    /**
     * Získá SQL
     *
     * @return  string
     */
    public function getSQL()
    {
        return implode("\n\n", $this->queries);
    }

    /**
     * Zkontroluje jestli tabulka existuje
     *
     * @param   string  $table  tabulka
     *
     * @return  bool
     */
    public function hasTable(string $table)
    {
        return isset($this->tables[$table]);
    }

    /**
     * Získá tabulku
     *
     * @param   string  $table  tabulka
     *
     * @return  Blueprint
     */
    public function getTable(string $table)
    {
        return $this->tables[$table];
    }

    /**
     * Zkontroluje jestli tabulka má sloupec
     *
     * @param   string  $table   tabulka
     * @param   string  $column  sloupec
     *
     * @return  bool
     */
    public function hasColumn(string $table, string $column)
    {
        return $this->hasTable($table) && $this->tables[$table]->columnExits($column);
    }

    /**
     * Získá názvy tabulek
     *
     * @return  array<string>
     */
    public function getTablesNames()
    {
        return array_keys($this->tables);
    }

    /**
     * Vrátí jméno tabulky i s předponou
     *
     * @param   string  $table  tabulka
     *
     * @return  string
     */
    public function formatTableName(string $table)
    {
        return $this->prefix . $table;
    }
}
