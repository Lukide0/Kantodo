<?php 

namespace Kantodo\Core\Database\Migration;

use Kantodo\Core\Database\Migration\Exception\TableException;

class TableChanges
{
    private $mod;
    private $orig;

    private $remove = ['columns' => [], 'unique' => [], 'foreign' => []];
    private $update = ['columns' => [], 'primary' => [], 'tableName' => ""];
    private $add    = ['columns' => [], 'unique' => [], 'foreign' => []];

    private $sql = NULL;

    public function __construct(Blueprint $original, Blueprint $modified) {
        $this->orig = $original;
        $this->mod = $modified;

        $this->FindChanges();
    }

    public function GetChanges()
    {
        return ['remove' => $this->remove, 'update' => $this->update, 'add' => $this->add];
    }

    public function GetChangesAsSQL() 
    {
        if ($this->sql != NULL)
            return $this->sql;

        $tmpSQL = "";
        
        // rename table
        $table = $this->orig->GetFullName();
        if (!empty($this->update['tableName'])) 
        {
            $tmpSQL .= "RENAME TABLE {$table} TO {$this->mod->GetFullName()}";
            $table  = $this->mod->GetFullName();
        }
        
        // drop foreign keys

        if (count($this->remove['foreign']) != 0) 
        {
            $tmpSQL .= "ALTER TABLE {$table}";
            foreach ($this->remove['foreign'] as $fk) {
                $tmpSQL .= " DROP FOREIGN KEY `{$fk['key']}`,";
            }
            $tmpSQL[strlen($tmpSQL) - 1] = ';';
        }
        
        // update primary key

        if (count($this->update['primary']) != 0) 
        {
            $tmpSQL .= "ALTER TABLE {$table} DROP PRIMARY KEY, ADD PRIMARY KEY (" . implode(",", array_map(function($key){ return "`{$key}`"; }, $this->update['primary'] )) . ");";
        }


        // drop unique keys
        if (count($this->remove['unique']) != 0) 
        {
            $tmpSQL .= "ALTER TABLE {$table}";
            
            foreach ($this->remove['unique'] as $key) {
                
                $key = "UN_" . implode("_",explode(";", $key));

                $tmpSQL .= " DROP INDEX {$key},";
            }
            
            $tmpSQL[strlen($tmpSQL) - 1] = ';';
        }

        // remove columns
        if (count($this->remove['columns']) != 0) 
        {
            $tmpSQL .= "ALTER TABLE {$table}";
            foreach ($this->remove['columns'] as $columnName) {
                $tmpSQL .= " DROP COLUMN {$columnName} CASCADE CONSTRAINTS,";
            }
    
            // last comma
            $tmpSQL[strlen($tmpSQL) - 1] = ';';
        }

        // modify column

        if (count($this->update['columns']) != 0) 
        {
            $tmpSQL .= "ALTER TABLE {$table}";

            foreach ($this->update['columns'] as $columnName => $opt) {
                $columnSQL = Blueprint::ColumnToSQL($columnName, $opt);
                $tmpSQL .= " MODIFY COLUMN {$columnSQL},";
            }

            $tmpSQL[strlen($tmpSQL) - 1] = ';';
        }


        // add columns

        if (count($this->add['columns']) != 0) 
        {
            $tmpSQL .= "ALTER TABLE {$table}";

            foreach ($this->add['columns'] as $name => $opt) {
                $columnSQL = Blueprint::ColumnToSQL($name, $opt);
                $tmpSQL .= " ADD COLUMN {$columnSQL},";
            }

            $tmpSQL[strlen($tmpSQL) - 1] = ';';
        }

        // add unique keys

        if (count($this->add['unique']) != 0) 
        {
            $tmpSQL .= "ALTER TABLE {$table}";
            
            foreach ($this->add['unique'] as $columns) {
                $columns = explode(";", $columns);
                $keysString = implode(',', $columns);
                $keyName = "UN_" . implode("_", $columns);
                $tmpSQL .= " ADD CONSTRAINT {$keyName} UNIQUE ({$keysString}),";
            }

            $tmpSQL[strlen($tmpSQL) - 1] = ';';
        }

        // add foreign keys
        if (count($this->add['foreign']) != 0) 
        {
            $tmpSQL .= "ALTER TABLE {$table}";
            
            foreach ($this->add['foreign'] as $column => $foreign) {
                " ADD CONSTRAINT `{$foreign['key']}` FOREIGN KEY (`{$column}`) REFERENCES {$foreign['table']}(`{$foreign['column']}`) ON DELETE {$foreign['onDelete']} ON UPDATE {$foreign['onUpdate']},";
            }

            $tmpSQL[strlen($tmpSQL) - 1] = ';';
        }        
        
        return $this->sql = $tmpSQL;
    }

    private function FindChanges() 
    {
        if (!$this->orig->IsValid()) 
            throw new TableException("Table `{$this->orig->GetName()}` is not valid");

        if (!$this->mod->IsValid())
            throw new TableException("Table `{$this->mod->GetName()}` is not valid");

        // name
        $rename = false;

        if ($this->orig->GetName() != $this->mod->GetName()) 
            $rename = true;
        
        // prefix
        if ($this->orig->GetPrefix() != $this->mod->GetPrefix() OR $rename === true) 
        {
            $this->update['tableName'] = $this->mod->GetFullName();
        }
        
        // columns
        $matches = 0;
        foreach ($this->orig->GetColumns() as $columnName => $options) 
        {
            if (!$this->mod->ColumnExits($columnName)) 
            {
                $this->remove['columns'][] = $columnName;
                continue;
            }
            
            $changes = Blueprint::CompareColumn($options, $this->mod->GetColumn($columnName));
            
            
            $matches++;
            if (count($changes) == 0)
                continue;
            
            $this->update['columns'][$columnName] = $this->mod->GetColumn($columnName);
        }

        if ($matches != count($this->mod->GetColumns())) 
        {
            foreach ($this->mod->GetColumns() as $columnName => $options) 
            {
                $this->add['columns'][$columnName] = $options;
            }
        }
        

        // primary keys

        $primaryOrig = $this->orig->GetPrimaryKeys();
        $primaryMod = $this->mod->GetPrimaryKeys();

        $diff = array_diff($primaryMod, $primaryOrig);
        if (count($diff) != 0)
            $this->update['primary'] = $primaryMod;

        $primaryOrig = $primaryMod = null;



        // unique keys
        $uniqueOrig = $this->orig->GetUniqueKeys();
        $uniqueMod = $this->mod->GetUniqueKeys();


        $this->remove['unique'] = array_diff($uniqueOrig, $uniqueMod);
        $this->add['unique'] = array_diff($uniqueMod, $uniqueOrig);

        $uniqueOrig = $uniqueMod = null;

        // foreign keys
        $foreignOrig = $this->orig->GetForeignKeys();
        $foreignMod = $this->mod->GetForeignKeys();

        $this->remove['foreign'] = array_diff_key($foreignOrig, $foreignMod);
        $this->add['foreign'] = array_diff_key($foreignMod, $foreignOrig);

        $foreignKeyOpt = ['table', 'column', 'onDelete', 'onUpdate'];
        foreach (array_intersect_key($foreignOrig, $foreignMod) as $key => $_) {
            $orig = $foreignOrig[$key];
            $mod = $foreignMod[$key];

            foreach ($foreignKeyOpt as $opt) {
                if ($orig[$opt] !== $mod[$opt]) 
                {
                    $this->remove['foreign'][] = $key;
                    $this->add['foreign'][$key] = $mod;
                    break;
                }
            }

        }
    }
}


?>