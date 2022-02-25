<?php

declare(strict_types=1);

namespace Kantodo\Core\Database\Migration;

use InvalidArgumentException;
use Kantodo\Core\Database\Migration\Exception\ColumnException;
use Kantodo\Core\Database\Migration\Exception\ForeignKeyException;
use Kantodo\Core\Database\Migration\Exception\TableException;

/**
 * Blueprint tabulky
 */
class Blueprint
{
    /**
     * Validní datové typy sloupce
     *
     * @var array<string>
     */
    const VALID_DATA_TYPES = [
        'CHAR',
        'VARCHAR',
        'BINARY',
        'VARBINARY',
        'TINYBLOB',
        'TINYTEXT',
        'TEXT',
        'BLOB',
        'MEDIUMTEXT',
        'MEDIUMBLOB',
        'LONGTEXT',
        'LONGBLOB',
        'ENUM',
        'SET',
        'BIT',
        'TINYINT',
        'BOOL',
        'SMALLINT',
        'MEDIUMINT',
        'INT',
        'BIGINT',
        'FLOAT',
        'DOUBLE',
        'DECIMAL',
        'DATE',
        'DATETIME',
        'TIMESTAMP',
        'TIME',
        'YEAR',
    ];

    const ACTION_NON      = 'RESTRICT';
    const ACTION_AFFECT   = 'CASCADE';
    const ACTION_SET_NULL = 'SET NULL';

    /**
     * Sloupce
     *
     * @var array<string,array<string,mixed>>
     */
    private $columns = [];

    /**
     * Primární klíče
     *
     * @var array<string>
     */
    private $primary = [];

    /**
     * Unikátní klíče
     *
     * @var array<string>
     */
    private $unique = [];

    /**
     * Foreign klíče
     *
     * @var array<string,array<string,mixed>>
     */
    private $foreign = [];

    /**
     * Název tabulky
     *
     * @var string
     */
    private $tableName;

    /**
     * Předpona tabulky
     *
     * @var string
     */
    private $prefix;

    public function __construct(string $tableName, string $prefix = '')
    {
        $this->tableName = $tableName;
        $this->prefix    = $prefix;
    }

    /**
     * přidá sloupec
     *
     * @param   string  $column   název sloupce
     * @param   string  $type     validní datový typ
     * @param   array<string,mixed>  $options  **vlastnosti:**
     *
     * - notNull         = bool      - výchozí true
     * - length          = int
     * - default         = string
     * - autoincrement   = bool      - výchozí false
     * - unique          = bool
     * - unsigned        = bool      - výchozí false
     *
     *
     *
     * @return  void
     *
     * @throws ColumnException pokud už sloupec existuje nebo datový typ není validní
     */
    public function addColumn(string $column, string $type, array $options = [])
    {

        if ($this->columnExits($column)) {
            throw new ColumnException("Column `$column` already exists in table `{$this->tableName}`");
        }

        $type = strtoupper($type);

        if (!in_array($type, self::VALID_DATA_TYPES, true)) {
            throw new ColumnException("Column data type `$type` is not supported");
        }

        switch ($type) {
            case 'BOOL':
                $type = 'TINYINT(1)';
                break;
            case 'TINYINT':
            case 'SMALLINT':
            case 'MEDIUMINT':
            case 'INT':
            case 'BIGINT':
                break;
            case 'VARCHAR':
                $options['length']   = $options['length'] ?? 255;
                $options['unsigned'] = false;
            default:
                $options['unsigned'] = false;
                break;
        }

        $this->columns[$column] = [
            'type'          => $type,
            'notNull'       => $options['notNull'] ?? true,
            'length'        => $options['length'] ?? null,
            'default'       => $options['default'] ?? null,
            'autoincrement' => $options['autoincrement'] ?? false,
            'unique'        => $options['unique'] ?? false,
            'unsigned'      => $options['unsigned'] ?? false,
        ];
    }

    /**
     * Modifikuje sloupec
     *
     * @see addColumn()
     *
     * @param   string  $column   název sloupce
     * @param   string  $type     validní datový typ
     * @param   array<string,mixed>   $options
     *
     * @return  self
     *
     */
    public function modifyColumn(string $column, string $type, array $options = [])
    {
        if (!$this->columnExits($column)) {
            throw new ColumnException("Column `$column` doesn't exists in table `{$this->tableName}`");
        }

        $type = strtoupper($type);

        if (!in_array($type, self::VALID_DATA_TYPES, true)) {
            throw new ColumnException("Column data type `$type` is not supported");
        }

        $this->columns[$column] = [
            'type'          => $type,
            'notNull'       => $options['notNull'] ?? true,
            'length'        => $options['length'] ?? null,
            'default'       => $options['default'] ?? null,
            'autoincrement' => $options['autoincrement'] ?? false,
            'unique'        => $options['unique'] ?? false,
            'unsigned'      => $options['unsigned'] ?? false,
        ];

        return $this;
    }

    /**
     * Odstraní sloupec
     *
     * @param   string  $column  jméno sloupce
     *
     * @return  void
     *
     * @throws ColumnException   pokud sloupec neexistuje
     */
    public function removeColumn(string $column)
    {
        if (!$this->columnExits($column)) {
            throw new ColumnException("Column `$column` doesn't exists in table `{$this->tableName}`");
        }

        $this->removeAllKeys($column);
        unset($this->columns[$column]);
    }

    /**
     * Odstraní všechny klíče, které jsou na sloupci
     *
     * @param   string  $column  sloupec
     *
     * @return  void
     *
     * @throws ColumnException   pokud sloupec neexistuje
     */
    public function removeAllKeys(string $column)
    {
        if (!$this->columnExits($column)) {
            throw new ColumnException("Column `$column` doesn't exists in table `{$this->tableName}`");
        }

        $this->removePrimaryKey($column);
        $this->removeForeignKey($column);
        $this->removeUnique($column);
    }

    /**
     * Přidá primární klíč
     *
     * @param   string  $column  název sloupce
     *
     * @return  bool             podařilo se přidat
     *
     * @throws ColumnException   pokud sloupec neexistuje
     */
    public function addPrimaryKey(string $column)
    {
        if (!$this->columnExits($column)) {
            throw new ColumnException("Column `$column` doesn't exists in table `{$this->tableName}`");
        }

        // pokud již existuje
        if (in_array($column, $this->primary, true)) {
            return false;
        }

        $this->primary[] = $column;
        return true;
    }

    /**
     * Odstraní primární klíč
     *
     * @param   string  $column  sloupec
     *
     * @return  bool             podařilo se odstranit
     *
     * @throws ColumnException   pokud sloupec neexistuje
     */
    public function removePrimaryKey(string $column)
    {
        if (!$this->columnExits($column)) {
            throw new ColumnException("Column `$column` doesn't exists in table `{$this->tableName}`");
        }

        // sloupec nemá primární klíč
        $index = array_search($column, $this->primary, true);
        if ($index === false) {
            return false;
        }

        unset($this->primary[$index]);
        return true;
    }

    /**
     * Přidá unikátní klíč
     *
     * @param   array<string>  $columns  sloupce, které budou tvořit klíč
     *
     * @return  void
     *
     * @throws ColumnException   pokud sloupec neexistuje
     */
    public function addUnique(array $columns)
    {
        sort($columns);

        foreach ($columns as $column) {
            if (!$this->columnExits($column)) {
                throw new ColumnException("Column `$column` doesn't exists in table `{$this->tableName}`");
            }
        }

        $key = implode(';', $columns);

        if (in_array($key, $this->unique, true)) {
            return;
        }

        $this->unique[] = $key;
    }

    /**
     * Odstraní unikátní klíč
     *
     * @param   array<string>|string  $columns  sloupec nebo sloupce
     *
     * @return  bool                       podařilo se odstranit
     *
     * @throws  InvalidArgumentException  pokud $columns není string ani array<string>
     */
    public function removeUnique($columns)
    {
        if (is_array($columns)) {
            sort($columns);

            $key = implode(';', $columns);

            $index = array_search($key, $this->unique, true);

            if ($index === false) {
                return false;
            }

            unset($this->unique[$index]);

            return true;
        }
        /** @phpstan-ignore-next-line */
        else if (is_string($columns)) {
            $match = false;
            foreach ($this->unique as $index => $key) {
                $keyColumns = explode(';', $key);

                if (in_array($columns, $keyColumns, true)) {
                    $match = true;
                    unset($this->unique[$index]);
                }
            }
            return $match;
        }

        /** @phpstan-ignore-next-line */
        $type = gettype($columns);
        throw new InvalidArgumentException("Expected string or array<string> `{$type}`");
    }

    /**
     * Vytvoří foreign klíč
     *
     *
     * @param   string      $column           sloupec
     * @param   Blueprint   $reference        dceřiná tabulka
     * @param   string      $referenceColumn  sloupec v tabulce
     * @param   string      $onDelete         ON DELETE akce
     * @param   string      $onUpdate         ON UPDATE akce
     *
     * @return bool
     *
     * @throws ColumnException                pokud sloupec nebo sloupec v dceřině tabulce neexistuje
     * @throws ForeignKeyException            pokud klíč už existuje
     */
    public function addForeignKey(string $column, Blueprint $reference, string $referenceColumn, string $onDelete = self::ACTION_NON, string $onUpdate = self::ACTION_NON)
    {
        if (!$this->columnExits($column)) {
            throw new ColumnException("Column `$column` doesn't exists in table `{$this->tableName}`");
        }

        if (!$reference->columnExits($referenceColumn)) {
            throw new ColumnException("Column `$referenceColumn` doesn't exists in table `{$reference->getName()}`");
        }

        if (isset($this->foreign[$column])) {
            return false;
        }

        $columnDataType    = $this->getColumn($column);
        $referenceDataType = $reference->getColumn($referenceColumn);

        if (
            $columnDataType['type'] != $referenceDataType['type'] ||
            $columnDataType['length'] != $referenceDataType['length'] ||
            $columnDataType['unsigned'] != $referenceDataType['unsigned']
        ) {
            throw new ColumnException("Column {$column} must have same data type as reference column {$referenceColumn} in table {$reference->getName()}.");
        }

        $this->foreign[$column] = [
            'key'      => "FK_{$this->tableName}_{$referenceColumn}",
            'table'    => $reference->getFullName(),
            'column'   => $referenceColumn,
            'onDelete' => $onDelete,
            'onUpdate' => $onUpdate,
        ];

        return true;
    }

    /**
     * Odstraní foreign klíč
     *
     * @param   string  $column  sloupec
     *
     * @return  bool             podařilo se odstranit
     *
     * @throws ColumnException   pokud sloupec neexistuje
     */
    public function removeForeignKey(string $column)
    {
        if (!$this->columnExits($column)) {
            throw new ColumnException("Column `$column` doesn't exists in table `{$this->tableName}`");
        }

        if (!isset($this->foreign[$column])) {
            return false;
        }

        unset($this->foreign[$column]);

        return true;
    }

    /**
     * Získá vlastnosti sloupce
     *
     * @param   string  $name  sloupec
     *
     * @return  array<string,mixed>          vlastnosti
     *
     * @see     addColumn
     */
    public function getColumn(string $name)
    {
        return $this->columns[$name];
    }

    /**
     * Existuje sloupec
     *
     * @param   string  $column  sloupec
     *
     * @return  bool
     */
    public function columnExits(string $column)
    {
        return isset($this->columns[$column]);
    }

    /**
     * Získá název tabulky
     *
     * @return  string
     */
    public function getName()
    {
        return $this->tableName;
    }

    /**
     * Získá předponu tabulky
     *
     * @return  string
     */
    public function getPrefix()
    {
        return $this->prefix;
    }

    /**
     * Získá název tabulky s předponou
     *
     * @return  string
     */
    public function getFullName()
    {
        return $this->prefix . $this->tableName;
    }

    /**
     * Získá sloupce z tabulky
     *
     * @return  array<string,mixed>  sloupce
     */
    public function getColumns()
    {
        return $this->columns;
    }

    /**
     * Získá primární klíče
     *
     * @return  array<string>
     */
    public function getPrimaryKeys()
    {
        return $this->primary;
    }

    /**
     * Získá unikátní klíče
     *
     * @param   bool   $formated  reálný název klíče
     *
     * @return  array<string>
     */
    public function getUniqueKeys(bool $formated = false)
    {
        if ($formated) {
            return array_map(function ($key) {
                $columns = explode(';', $key);
                return 'UN_' . implode('_', $columns);
            }, $this->unique);
        }

        return $this->unique;
    }

    /**
     * Získá foreign klíče
     *
     * @return  array<string,mixed>
     */
    public function getForeignKeys()
    {
        return $this->foreign;
    }

    /**
     * Získá vlastnosti tabulky
     * vlastnosti:
     * -table           = string      - název tabulky
     * -prefix          = string      - předpona tabulky
     * -columns         = array       - sloupce
     * -primary         = array       - primární klíče
     * -unique          = array       - unikátní klíče
     * -foreign         = array       - foreign klíče
     *
     * @return  array<string,mixed>
     */
    public function get()
    {
        return [
            'table'   => $this->tableName,
            'prefix'  => $this->prefix,
            'columns' => $this->columns,
            'primary' => $this->primary,
            'unique'  => $this->unique,
            'foreign' => $this->foreign,
        ];
    }

    /**
     * Porovná sloupce
     *
     * @param   array<string,mixed>  $original  originální sloupec
     * @param   array<string,mixed>  $modified  modifikovaný sloupec
     *
     * @return  array<string,mixed>             změny
     */
    public static function compareColumn(array $original, array $modified)
    {
        $changes = [];

        $options = ['type', 'notNull', 'length', 'default', 'autoincrement', 'unique', 'unsigned'];

        foreach ($options as $option) {
            if ($original[$option] !== $modified[$option]) {
                $changes[$option] = $modified[$option];
            }
        }

        return $changes;
    }

    /**
     * Vytvoří SQL
     *
     * @return  string          SQL tabulky
     *
     * @throws TableException  pokud tabulka není validní
     *
     * @see                     isValid
     */
    public function createTableSQL()
    {
        if (count($this->columns) == 0) {
            throw new TableException("Table `{$this->tableName}` has 0 columns");
        }

        if (count($this->primary) == 0) {
            throw new TableException("Primary key is not set in table `{$this->tableName}`");
        }

        $sqlColumns = [];
        foreach ($this->columns as $columnName => $columnOptions) {
            $sqlColumns[] = self::columnToSQL($columnName, $columnOptions);
        }

        $sqlKeys   = array();
        $sqlKeys[] = 'PRIMARY KEY (' . implode(',', array_map([$this, 'GetStringInBackticks'], $this->primary)) . ')';
        foreach ($this->unique as $key) {
            $keys       = explode(';', $key);
            $keysString = implode(',', array_map([$this, 'GetStringInBackticks'], $keys));
            $keyName    = '`UN_' . implode('_', $keys) . '`';

            $sqlKeys[] = "CONSTRAINT {$keyName} UNIQUE ({$keysString})";
        }

        foreach ($this->foreign as $column => $foreign) {
            $sqlKeys[] = "CONSTRAINT `{$foreign['key']}` FOREIGN KEY (`{$column}`) REFERENCES {$foreign['table']}(`{$foreign['column']}`) ON DELETE {$foreign['onDelete']} ON UPDATE {$foreign['onUpdate']}";
        }

        $sql = "CREATE TABLE {$this->getFullName()} (\n" . implode(",\n", $sqlColumns) . ",\n\t" . implode(",\n\t", $sqlKeys) . "\n) ENGINE = INNODB DEFAULT CHARSET=utf8;";
        return $sql;
    }

    /**
     * Vrací string v ``
     *
     * @param   string  $s  text
     *
     * @return  string
     */
    public static function getStringInBackticks(string $s)
    {
        return "`{$s}`";
    }

    /**
     * Sloupce do SQL
     *
     * @param   string  $column  sloupec
     * @param   array<string,mixed>   $opt     vlastnosti sloupce
     *
     * @return  string           SQL
     */
    public static function columnToSQL(string $column, array $opt)
    {
        $sqlColumn = "  `{$column}` {$opt['type']}";

        if ($opt['length'] != null) {
            $sqlColumn .= "({$opt['length']})";
        }

        $sqlColumn .= ' ';

        if ($opt['unsigned'] == true) {
            $sqlColumn .= 'UNSIGNED ';
        }

        if ($opt['notNull'] == true) {
            $sqlColumn .= 'NOT NULL ';
        }

        if ($opt['default'] != null) {
            $sqlColumn .= "DEFAULT {$opt['default']} ";
        }

        if ($opt['autoincrement'] == true) {
            $sqlColumn .= 'AUTO_INCREMENT ';
        }

        if ($opt['unique'] == true) {
            $sqlColumn .= 'UNIQUE';
        }

        return $sqlColumn;
    }

    /**
     * Je tabulka validní pokud obsahuje min. 1 sloupec a 1 primární klíč.
     *
     * @return  bool
     */
    public function isValid()
    {
        if (count($this->columns) == 0) {
            return false;
        }

        if (count($this->primary) == 0) {
            return false;
        }

        return true;
    }
}
