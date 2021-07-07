<?php 

namespace Kantodo\Core\Database\Migration;

use Kantodo\Core\Application;

abstract class AbstractMigration
{

    /**
     * @var Schema
     */
    private $schema;

    final public function __construct(Schema $schema = null) 
    {
        $this->schema = $schema;
    }

    public function GetSQL() 
    {
        return $this->schema->GetSQL();
    }

    public static function SaveSchema(Schema $schema) 
    {
        $objSer = serialize($schema);

        return file_put_contents(Application::$MIGRATION_DIR . "/currentSchema.ser", $objSer);
    }

    public static function LoadSchema() 
    {
        if (!file_exists(Application::$MIGRATION_DIR . "/currentSchema.ser"))
            return new Schema(Application::$DB_TABLE_PREFIX);

        return unserialize(file_get_contents(Application::$MIGRATION_DIR . "/currentSchema.ser"));

    }

    public abstract function Up(Schema $schema);
    public abstract function Down(Schema $schema);
}


?>