<?php 

namespace Kantodo\Core\Database\Migration;

use Kantodo\Core\Application;

abstract class AbstractMigration
{

    /**
     * @var Schema
     */
    private $schema;

    final public function __construct(Schema $schema = NULL) 
    {
        $this->schema = $schema;
    }

    public function getSQL() 
    {
        return $this->schema->getSQL();
    }

    public static function saveSchema(Schema $schema) 
    {
        $objSer = serialize($schema);

        return file_put_contents(Application::$MIGRATION_DIR . "/currentSchema.ser", $objSer);
    }

    public static function loadSchema() 
    {
        if (!file_exists(Application::$MIGRATION_DIR . "/currentSchema.ser"))
            return new Schema(Application::$DB_TABLE_PREFIX);

        return unserialize(file_get_contents(Application::$MIGRATION_DIR . "/currentSchema.ser"));

    }

    public abstract function up(Schema $schema);
    public abstract function down(Schema $schema);
}


?>