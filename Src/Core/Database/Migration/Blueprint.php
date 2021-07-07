<?php 

namespace Kantodo\Core\Database\Migration;

use InvalidArgumentException;
use Kantodo\Core\Database\Migration\Exception\{
    ColumnException,
    ForeignKeyException,
    TableException
};

class Blueprint
{
    
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
        'YEAR'
    ];
    
    const ACTION_NON = 'RESTRICT';
    const ACTION_AFFECT_CHILDREN = 'CASCADE';
    const ACTION_SET_NULL = 'SET NULL';

    
    protected $columns = [];
    protected $primary = [];
    protected $unique  = [];
    protected $foreign = [];
    protected $tableName;
    protected $prefix;

    public function __construct(string $tableName, string $prefix = '') {
        $this->tableName = $tableName;
        $this->prefix = $prefix;
    }
    
    /**
     * Add column to table
     * options:
     *      notNull         = bool      - default true
     *      length          = int       
     *      default         = string
     *      autoincrement   = bool      - defautl false
     *      unique          = bool
     *      unsigned        = bool      - default false
     *
     * @param   string  $column     column name
     * @param   string  $type     valid data type
     * @param   array   $options  (see above)
     *
     * @return  self 
     */
    public function AddColumn(string $column, string $type, array $options = [])
    {

        if ($this->ColumnExits($column))
            throw new ColumnException("Column `$column` already exists in table `{$this->tableName}`");

        $type = strtoupper($type);

        if (!in_array($type, self::VALID_DATA_TYPES))
            throw new ColumnException("Column data type `$type` is not supported");

        $this->columns[$column] = [
            'type' => $type,
            'notNull' => $options['notNull'] ?? true,
            'length' => $options['length'] ?? NULL,
            'default' => $options['default'] ?? NULL,
            'autoincrement' => $options['autoincrement'] ?? false,
            'unique' => $options['unique'] ?? false,
            'unsigned' => $options['unsigned'] ?? false
        ];
    }

    /**
     * Modify column in table
     * options:
     *      notNull         = bool      - default true
     *      length          = int       
     *      default         = string
     *      autoincrement   = bool      - defautl false
     *      unique          = bool
     *      unsigned        = bool      - default false
     * 
     * @param   string  $column     column name
     * @param   string  $type     valid data type
     * @param   array   $options  (see above)
     *
     * @return  self 
     * 
     */
    public function ModifyColumn(string $column, string $type, array $options = []) 
    {
        if (!$this->ColumnExits($column))
            throw new ColumnException("Column `$column` doesn't exists in table `{$this->tableName}`");
        
        $type = strtoupper($type);

        if (!in_array($type, self::VALID_DATA_TYPES))
            throw new ColumnException("Column data type `$type` is not supported");

        $this->columns[$column] = [
            'type' => $type,
            'notNull' => $options['notNull'] ?? true,
            'length' => $options['length'] ?? NULL,
            'default' => $options['default'] ?? NULL,
            'autoincrement' => $options['autoincrement'] ?? false,
            'unique' => $options['unique'] ?? false,
            'unsigned' => $options['unsigned'] ?? false
        ];
    }


    public function RemoveColumn(string $column)
    {
        if (!$this->ColumnExits($column))
            throw new ColumnException("Column `$column` doesn't exists in table `{$this->tableName}`");
        
        
            
        $this->RemoveAllKeys($column);
        unset($this->columns[$column]);

        return true;
    }

    public function RemoveAllKeys(string $column) 
    {
        if (!$this->ColumnExits($column))
            throw new ColumnException("Column `$column` doesn't exists in table `{$this->tableName}`");
        
        $this->RemovePrimaryKey($column);
        $this->RemoveForeignKey($column);
        $this->RemoveUnique($column);
    }

    public function AddPrimaryKey(string $column) 
    {
        if (!$this->ColumnExits($column))
            throw new ColumnException("Column `$column` doesn't exists in table `{$this->tableName}`");
        
        // column is primary key
        if (in_array($column, $this->primary))
            return false;
        
        // add primary key
        $this->primary[] = $column;
        return true;
    }

    public function RemovePrimaryKey(string $column) 
    {
        if (!$this->ColumnExits($column))
            throw new ColumnException("Column `$column` doesn't exists in table `{$this->tableName}`");
        
        // column is not primary key
        $index = array_search($column, $this->primary);
        if ($index === false)
            return false;

        unset($this->primary[$index]);
        return true;
    }

    public function AddUnique(array $columns) 
    {
        sort($columns);

        $key = implode(";", $columns);

        if (in_array($key, $this->unique))
            return;

        $this->unique[] = $key;
    }

    public function RemoveUnique($columns)
    {
        if (is_array($columns)) 
        {
            sort($columns);
            
            $key = implode(";", $columns);
    
            $index = array_search($key, $this->unique);
    
            if ($index === false)
                return false;
            
            unset($this->unique[$index]);

            return true;
        }

        if (is_string($columns)) 
        {
            $match = false;
            foreach ($this->unique as $index => $key) {
                $keyColumns = explode(";", $key);

                if (in_array($columns, $keyColumns)) 
                {
                    $match = true;
                    unset($this->unique[$index]);
                }
            }
            return $match;
        }
        $type = gettype($columns);
        throw new InvalidArgumentException("Expected string or array<string> `{$type}`");             
    } 

    /**
     * Create foreign key
     *
     * 
     * @param   string      $column           column name
     * @param   Blueprint   $reference        reference table
     * @param   string      $referenceColumn  column in reference table
     * @param   string      $onDelete         MySQL on delete action
     * @param   string      $onUpdate         MySQL on update action
     *
     * @throws ColumnException When column or reference column doesn't exists
     * @throws ForeignKeyException When foreign key already exists
     */
    public function AddForeignKey(string $column, Blueprint $reference, string $referenceColumn, string $onDelete = self::ACTION_NON, string $onUpdate = self::ACTION_NON) 
    {
        if (!$this->ColumnExits($column))
            throw new ColumnException("Column `$column` doesn't exists in table `{$this->tableName}`");
        
        if (!$reference->ColumnExits($referenceColumn))
            throw new ColumnException("Column `$referenceColumn` doesn't exists in table `{$reference->GetName()}`");

        if (isset($this->foreign[$column]))
            return false;
        
        $this->foreign[$column] = [
            'key' => "FK_{$reference->GetFullName()}{$referenceColumn}",
            'table' => $reference->GetFullName(),
            'column' => $referenceColumn,
            'onDelete' => $onDelete,
            'onUpdate' => $onUpdate
        ];

        return true;
    }

    public function RemoveForeignKey(string $column)
    {
        if (!$this->ColumnExits($column))
            throw new ColumnException("Column `$column` doesn't exists in table `{$this->tableName}`");
        
        if (!isset($this->foreign[$column]))
            return false;
        
        unset($this->foreign[$column]);

        return true;
    }

    public function GetColumn(string $name) 
    {
        return $this->columns[$name] ?? false;
    }

    public function ColumnExits(string $column) 
    {
        return isset($this->columns[$column]);

    }

    /**
     * Get table name
     *
     * @return  string  table name
     */
    public function GetName() 
    {
        return $this->tableName;
    }

    public function GetPrefix()
    {
        return $this->prefix;
    }

    public function GetFullName() 
    {
        return $this->prefix . $this->tableName;
    }

    public function GetColumns() 
    {
        return $this->columns;
    }

    public function GetPrimaryKeys() 
    {
        return $this->primary;
    }

    public function GetUniqueKeys(bool $formated = false) 
    {
        if ($formated)
            return array_map(function($key)
            {
                $columns = explode(";", $key);
                return "UN_" . implode("_",$columns);
            }, $this->unique);
        return $this->unique;
    }

    public function GetForeignKeys() 
    {
        return $this->foreign;
    }

    /**
     * Get table detail
     * details:
     *      table           = string      - table name without prefix
     *      prefix          = string      - table prefix
     *      columns         = array       - all columns
     *      primary         = array       - primary columns in ``
     *      unique          = array       - unique keys
     *      foreign         = array       - foreign keys
     *
     * @see GetColumn
     * @return  array  table detail (see above)
     */
    public function Get() 
    {
        return [
            'table' => $this->tableName,
            'prefix' => $this->prefix,
            'columns' => $this->columns,
            'primary' => $this->primary,
            'unique' => $this->unique,
            'foreign' => $this->foreign
        ];
    }

    /**
     * Compare columns
     *
     * @param   array  $original  not modified column
     * @param   array  $modified  modified column
     *
     * @return  array            array of changes
     */
    public static function CompareColumn(array $original, array $modified)
    {
        $changes = [];

        $options = ['type', 'notNull', 'length', 'default', 'autoincrement', 'unique', 'unsigned'];

        foreach ($options as $option) {
            if ($original[$option] !== $modified[$option])
                $changes[$option] = $modified[$option];
        }

        return $changes;
    }

    /**
     * Create sql
     * 
     * @return  string  table sql
     * 
     * @throws ColumnException When is not set primary key
     */
    public function CreateTableSQL()
    {
        if (count($this->columns) == 0) 
            throw new TableException("Table `{$this->tableName}` has 0 columns");

        if (count($this->primary) == 0)
            throw new TableException("Primary key is not set in table `{$this->tableName}`");

        $sqlColumns = [];
        foreach ($this->columns as $columnName => $columnOptions) {            
            $sqlColumns[] = self::ColumnToSQL($columnName, $columnOptions);
        }

        $sqlKeys = array();
        $sqlKeys[] = "PRIMARY KEY (" . implode(",",
                                                    array_map(function($key){ return "`{$key}`"; },
                                                            $this->primary
                                                    )
                                            ) . ")";
        foreach ($this->unique as $key) {
            $keys = explode(";", $key);
            $keysString = implode(',', $keys);
            $keyName = "UN_" . implode("_", $keys);

            $sqlKeys[] = "CONSTRAINT {$keyName} UNIQUE ({$keysString})";
        }

        foreach ($this->foreign as $column => $foreign) {
            $sqlKeys[] = "CONSTRAINT `{$foreign['key']}` FOREIGN KEY (`{$column}`) REFERENCES {$foreign['table']}(`{$foreign['column']}`) ON DELETE {$foreign['onDelete']} ON UPDATE {$foreign['onUpdate']}";
        }

        $sql = "CREATE TABLE {$this->GetFullName()} (\n" . implode(",\n",$sqlColumns) . "," . implode(",", $sqlKeys) . ") ENGINE = INNODB DEFAULT CHARSET=utf8;";
        return $sql;
    }

    public static function ColumnToSQL(string $column, array $opt) 
    {
        $sqlColumn = "  `{$column}` {$opt['type']}";

        if ($opt['length'] != NULL)
            $sqlColumn .= "({$opt['length']})";
        $sqlColumn .= ' ';
        
        if ($opt['unsigned'] == true)
            $sqlColumn .= "UNSIGNED ";

        if ($opt['notNull'] == true)
            $sqlColumn .= "NOT NULL ";
        
        if ($opt['default'] != NULL)
            $sqlColumn .= "DEFAULT {$opt['default']} ";
        
        if ($opt['autoincrement'] == true)
            $sqlColumn .= "AUTO_INCREMENT";
        return $sqlColumn;
    }

    public function IsValid() 
    {
        if (count($this->columns) == 0) 
            return false;

        if (count($this->primary) == 0)
            return false;
        return true;
    }
}



?>