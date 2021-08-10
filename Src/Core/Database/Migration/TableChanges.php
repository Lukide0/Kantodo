<?php

namespace Kantodo\Core\Database\Migration;

use Kantodo\Core\Database\Migration\Exception\TableException;

/**
 * Třída na porovnání tabulek
 */
class TableChanges
{
    /**
     * Modifikovaná tabulka
     *
     * @var Blueprint
     */
    private $mod;

    /**
     * originální tabulka
     *
     * @var Blueprint
     */
    private $orig;

    // změny
    private $remove = ['columns' => [], 'unique' => [], 'foreign' => []];
    private $update = ['columns' => [], 'primary' => [], 'tableName' => ""];
    private $add    = ['columns' => [], 'unique' => [], 'foreign' => []];

    private $sql = null;

    public function __construct(Blueprint $original, Blueprint $modified)
    {
        $this->orig = $original;
        $this->mod  = $modified;

        $this->findChanges();
    }

    /**
     * Získá změny
     *
     * @return  array  ['remove', 'update', 'add']
     */
    public function getChanges()
    {
        return ['remove' => $this->remove, 'update' => $this->update, 'add' => $this->add];
    }

    /**
     * Získá změny jako SQL
     *
     * @return  string
     */
    public function getChangesAsSQL()
    {
        if ($this->sql != null) {
            return $this->sql;
        }

        $tmpSQL = '';

        // rename table
        $table = $this->orig->getFullName();
        if (!empty($this->update['tableName'])) {
            $tmpSQL .= "RENAME TABLE {$table} TO {$this->mod->getFullName()}";
            $table = $this->mod->getFullName();
        }

        // drop foreign klíčů
        if (count($this->remove['foreign']) != 0) {
            $tmpSQL .= "ALTER TABLE {$table}";
            foreach ($this->remove['foreign'] as $fk) {
                $tmpSQL .= " DROP FOREIGN KEY `{$fk['key']}`,";
            }
            $tmpSQL[strlen($tmpSQL) - 1] = ';';
        }

        // primary key
        if (count($this->update['primary']) != 0) {
            $tmpSQL .= "ALTER TABLE {$table} DROP PRIMARY KEY, ADD PRIMARY KEY (" . implode(",", array_map(function ($key) {return "`{$key}`";}, $this->update['primary'])) . ");";
        }

        // drop unikátní klíče
        if (count($this->remove['unique']) != 0) {
            $tmpSQL .= "ALTER TABLE {$table}";

            foreach ($this->remove['unique'] as $key) {

                $key = "UN_" . implode("_", explode(";", $key));

                $tmpSQL .= " DROP INDEX {$key},";
            }

            $tmpSQL[strlen($tmpSQL) - 1] = ';';
        }

        // odstranění sloupců
        if (count($this->remove['columns']) != 0) {
            $tmpSQL .= "ALTER TABLE {$table}";
            foreach ($this->remove['columns'] as $columnName) {
                $tmpSQL .= " DROP COLUMN {$columnName} CASCADE CONSTRAINTS,";
            }

            // poslední čárka
            $tmpSQL[strlen($tmpSQL) - 1] = ';';
        }

        // modifikace sloupce
        if (count($this->update['columns']) != 0) {
            $tmpSQL .= "ALTER TABLE {$table}";

            foreach ($this->update['columns'] as $columnName => $opt) {
                $columnSQL = Blueprint::columnToSQL($columnName, $opt);
                $tmpSQL .= " MODIFY COLUMN {$columnSQL},";
            }

            $tmpSQL[strlen($tmpSQL) - 1] = ';';
        }

        // přidání sloupce
        if (count($this->add['columns']) != 0) {
            $tmpSQL .= "ALTER TABLE {$table}";

            foreach ($this->add['columns'] as $name => $opt) {
                $columnSQL = Blueprint::columnToSQL($name, $opt);
                $tmpSQL .= " ADD COLUMN {$columnSQL},";
            }

            $tmpSQL[strlen($tmpSQL) - 1] = ';';
        }

        // přidání unikátní klíčů
        if (count($this->add['unique']) != 0) {
            $tmpSQL .= "ALTER TABLE {$table}";

            foreach ($this->add['unique'] as $columns) {
                $columns    = explode(';', $columns);
                $keysString = implode(',', $columns);
                $keyName    = 'UN_' . implode('_', $columns);
                $tmpSQL .= " ADD CONSTRAINT {$keyName} UNIQUE ({$keysString}),";
            }

            $tmpSQL[strlen($tmpSQL) - 1] = ';';
        }

        // přidání foreign klíčů
        if (count($this->add['foreign']) != 0) {
            $tmpSQL .= "ALTER TABLE {$table}";

            foreach ($this->add['foreign'] as $column => $foreign) {
                " ADD CONSTRAINT `{$foreign['key']}` FOREIGN KEY (`{$column}`) REFERENCES {$foreign['table']}(`{$foreign['column']}`) ON DELETE {$foreign['onDelete']} ON UPDATE {$foreign['onUpdate']},";
            }

            $tmpSQL[strlen($tmpSQL) - 1] = ';';
        }

        return $this->sql = $tmpSQL;
    }

    /**
     * Najde a uloží všechny změny
     *
     * @return  void
     *
     * @throws TableChanges pokud jedna z tabulek není validní
     */
    private function findChanges()
    {
        if (!$this->orig->isValid()) {
            throw new TableException("Table `{$this->orig->getName()}` is not valid");
        }

        if (!$this->mod->isValid()) {
            throw new TableException("Table `{$this->mod->getName()}` is not valid");
        }

        // jméno tabulky
        $rename = false;

        if ($this->orig->getName() != $this->mod->getName()) {
            $rename = true;
        }

        // prefix
        if ($this->orig->getPrefix() != $this->mod->getPrefix() || $rename === true) {
            $this->update['tableName'] = $this->mod->getFullName();
        }

        // sloupce
        $matches = 0;
        foreach ($this->orig->getColumns() as $columnName => $options) {
            if (!$this->mod->columnExits($columnName)) {
                $this->remove['columns'][] = $columnName;
                continue;
            }

            $changes = Blueprint::compareColumn($options, $this->mod->getColumn($columnName));

            $matches++;
            if (count($changes) == 0) {
                continue;
            }

            $this->update['columns'][$columnName] = $this->mod->getColumn($columnName);
        }

        if ($matches != count($this->mod->getColumns())) {
            foreach ($this->mod->getColumns() as $columnName => $options) {
                $this->add['columns'][$columnName] = $options;
            }
        }

        // primární klíče
        $primaryOrig = $this->orig->getPrimaryKeys();
        $primaryMod  = $this->mod->getPrimaryKeys();

        $diff = array_diff($primaryMod, $primaryOrig);
        if (count($diff) != 0) {
            $this->update['primary'] = $primaryMod;
        }

        $primaryOrig = $primaryMod = null;

        // unikátní klíče
        $uniqueOrig = $this->orig->getUniqueKeys();
        $uniqueMod  = $this->mod->getUniqueKeys();

        $this->remove['unique'] = array_diff($uniqueOrig, $uniqueMod);
        $this->add['unique']    = array_diff($uniqueMod, $uniqueOrig);

        $uniqueOrig = $uniqueMod = null;

        // foreign klíče
        $foreignOrig = $this->orig->getForeignKeys();
        $foreignMod  = $this->mod->getForeignKeys();

        $this->remove['foreign'] = array_diff_key($foreignOrig, $foreignMod);
        $this->add['foreign']    = array_diff_key($foreignMod, $foreignOrig);

        $foreignKeyOpt = ['table', 'column', 'onDelete', 'onUpdate'];
        foreach (array_intersect_key($foreignOrig, $foreignMod) as $key => $_) {
            $orig = $foreignOrig[$key];
            $mod  = $foreignMod[$key];

            foreach ($foreignKeyOpt as $opt) {
                if ($orig[$opt] !== $mod[$opt]) {
                    $this->remove['foreign'][]  = $key;
                    $this->add['foreign'][$key] = $mod;
                    break;
                }
            }

        }
    }
}
