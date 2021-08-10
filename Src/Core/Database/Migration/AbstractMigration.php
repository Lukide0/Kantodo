<?php

namespace Kantodo\Core\Database\Migration;

use Kantodo\Core\Application;

/**
 * Základ migrace
 */
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

    /**
     * Schéma jako SQL
     *
     * @return  string  vrací schéma jako SQL
     */
    public function getSQL()
    {
        return $this->schema->getSQL();
    }

    /**
     * Uloží schéma do souboru
     *
     * @param   Schema  $schema  schéma k uložení
     *
     * @return  bool             status
     */
    public static function saveSchema(Schema $schema)
    {
        $schema->clearSQL();

        $objSer = serialize($schema);

        return file_put_contents(Application::$MIGRATION_DIR . '/currentSchema.ser', $objSer) !== false;
    }

    /**
     * Načte schéma ze souboru
     *
     * @return  Schema
     */
    public static function loadSchema()
    {
        if (!file_exists(Application::$MIGRATION_DIR . '/currentSchema.ser')) {
            return new Schema(Application::$DB_TABLE_PREFIX);
        }

        return unserialize(file_get_contents(Application::$MIGRATION_DIR . '/currentSchema.ser'));
    }

    /**
     * Update
     *
     * @param   Schema  $schema
     *
     * @return  void
     */
    abstract public function up(Schema $schema);

    /**
     * Downgrade
     *
     * @param   Schema  $schema
     *
     * @return  void
     */
    abstract public function down(Schema $schema);
}
