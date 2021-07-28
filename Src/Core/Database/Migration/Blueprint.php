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
    const ACTION_AFFECT = 'CASCADE';
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
    public function addColumn(string $column, string $type, array $options = [])
    {

        if ($this->columnExits($column))
            throw new ColumnException("Column `$column` already exists in table `{$this->tableName}`");

        $type = strtoupper($type);

        if (!in_array($type, self::VALID_DATA_TYPES))
            throw new ColumnException("Column data type `$type` is not supported");

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
                $options['length'] = $options['length'] ?? 255;
                $options['unsigned'] = false;
            default:
                $options['unsigned'] = false;
                break;
        }


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
    public function modifyColumn(string $column, string $type, array $options = []) 
    {
        if (!$this->columnExits($column))
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


    public function removeColumn(string $column)
    {
        if (!$this->columnExits($column))
            throw new ColumnException("Column `$column` doesn't exists in table `{$this->tableName}`");
        
        
            
        $this->removeAllKeys($column);
        unset($this->columns[$column]);

        return true;
    }

    public function removeAllKeys(string $column) 
    {
        if (!$this->columnExits($column))
            throw new ColumnException("Column `$column` doesn't exists in table `{$this->tableName}`");
        
        $this->removePrimaryKey($column);
        $this->removeForeignKey($column);
        $this->removeUnique($column);
    }

    public function addPrimaryKey(string $column) 
    {
        if (!$this->columnExits($column))
            throw new ColumnException("Column `$column` doesn't exists in table `{$this->tableName}`");
        
        // column is primary key
        if (in_array($column, $this->primary))
            return false;
        
        // add primary key
        $this->primary[] = $column;
        return true;
    }

    public function removePrimaryKey(string $column) 
    {
        if (!$this->columnExits($column))
            throw new ColumnException("Column `$column` doesn't exists in table `{$this->tableName}`");
        
        // column is not primary key
        $index = array_search($column, $this->primary);
        if ($index === false)
            return false;

        unset($this->primary[$index]);
        return true;
    }

    public function addUnique(array $columns) 
    {
        sort($columns);

        $key = implode(';', $columns);

        if (in_array($key, $this->unique))
            return;

        $this->unique[] = $key;
    }

    public function removeUnique($columns)
    {
        if (is_array($columns)) 
        {
            sort($columns);
            
            $key = implode(';', $columns);
    
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
                $keyColumns = explode(';', $key);

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
    public function addForeignKey(string $column, Blueprint $reference, string $referenceColumn, string $onDelete = self::ACTION_NON, string $onUpdate = self::ACTION_NON) 
    {
        if (!$this->columnExits($column))
            throw new ColumnException("Column `$column` doesn't exists in table `{$this->tableName}`");
        
        if (!$reference->columnExits($referenceColumn))
            throw new ColumnException("Column `$referenceColumn` doesn't exists in table `{$reference->getName()}`");

        if (isset($this->foreign[$column]))
            return false;
        
        $columnDataType = $this->getColumn($column);
        $referenceDataType = $reference->getColumn($referenceColumn);

        if ($columnDataType['type'] != $referenceDataType['type'] || 
            $columnDataType['length'] != $referenceDataType['length'] || 
            $columnDataType['unsigned'] != $referenceDataType['unsigned'])
            throw new ColumnException("Column {$column} must have same data type as reference column {$reference} in table {$reference->getName()}.");


        $this->foreign[$column] = [
            'key' => "FK_{$this->tableName}_{$referenceColumn}",
            'table' => $reference->getFullName(),
            'column' => $referenceColumn,
            'onDelete' => $onDelete,
            'onUpdate' => $onUpdate
        ];

        return true;
    }

    public function removeForeignKey(string $column)
    {
        if (!$this->columnExits($column))
            throw new ColumnException("Column `$column` doesn't exists in table `{$this->tableName}`");
        
        if (!isset($this->foreign[$column]))
            return false;
        
        unset($this->foreign[$column]);

        return true;
    }

    public function getColumn(string $name) 
    {
        return $this->columns[$name];
    }

    public function columnExits(string $column) 
    {
        return isset($this->columns[$column]);

    }

    /**
     * Get table name
     *
     * @return  string  table name
     */
    public function getName() 
    {
        return $this->tableName;
    }

    public function getPrefix()
    {
        return $this->prefix;
    }

    public function getFullName() 
    {
        return $this->prefix . $this->tableName;
    }

    public function getColumns() 
    {
        return $this->columns;
    }

    public function getPrimaryKeys() 
    {
        return $this->primary;
    }

    public function getUniqueKeys(bool $formated = false) 
    {
        if ($formated)
            return array_map(function($key)
            {
                $columns = explode(';', $key);
                return 'UN_' . implode('_',$columns);
            }, $this->unique);
        return $this->unique;
    }

    public function getForeignKeys() 
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
    public function get() 
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
    public static function compareColumn(array $original, array $modified)
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
    public function createTableSQL()
    {
        if (count($this->columns) == 0) 
            throw new TableException("Table `{$this->tableName}` has 0 columns");

        if (count($this->primary) == 0)
            throw new TableException("Primary key is not set in table `{$this->tableName}`");

        $sqlColumns = [];
        foreach ($this->columns as $columnName => $columnOptions) {            
            $sqlColumns[] = self::columnToSQL($columnName, $columnOptions);
        }

        $sqlKeys = array();
        $sqlKeys[] = 'PRIMARY KEY (' . implode(',',array_map([$this,'GetStringInBackticks'], $this->primary)) . ')';
        foreach ($this->unique as $key) {
            $keys = explode(';', $key);
            $keysString = implode(',', array_map([$this, 'GetStringInBackticks'], $keys));
            $keyName = '`UN_' . implode('_', $keys) . '`';

            $sqlKeys[] = "CONSTRAINT {$keyName} UNIQUE ({$keysString})";
        }

        foreach ($this->foreign as $column => $foreign) {
            $sqlKeys[] = "CONSTRAINT `{$foreign['key']}` FOREIGN KEY (`{$column}`) REFERENCES {$foreign['table']}(`{$foreign['column']}`) ON DELETE {$foreign['onDelete']} ON UPDATE {$foreign['onUpdate']}";
        }

        $sql = "CREATE TABLE {$this->getFullName()} (\n" . implode(",\n",$sqlColumns) . ",\n\t" . implode(",\n\t", $sqlKeys) . ') ENGINE = INNODB DEFAULT CHARSET=utf8;';
        return $sql;
    }

    public static function getStringInBackticks(string $s) 
    {
        return "`{$s}`";
    }

    public static function columnToSQL(string $column, array $opt) 
    {
        $sqlColumn = "  `{$column}` {$opt['type']}";

        if ($opt['length'] != NULL)
            $sqlColumn .= "({$opt['length']})";
        $sqlColumn .= ' ';
        
        if ($opt['unsigned'] == true)
            $sqlColumn .= 'UNSIGNED ';

        if ($opt['notNull'] == true)
            $sqlColumn .= 'NOT NULL ';
        
        if ($opt['default'] != NULL)
            $sqlColumn .= "DEFAULT {$opt['default']} ";
        
        if ($opt['autoincrement'] == true)
            $sqlColumn .= 'AUTO_INCREMENT ';
        
        if ($opt['unique'] == true)
            $sqlColumn .= 'UNIQUE';

        return $sqlColumn;
    }

    public function isValid() 
    {
        if (count($this->columns) == 0) 
            return false;

        if (count($this->primary) == 0)
            return false;
        return true;
    }
}



?>