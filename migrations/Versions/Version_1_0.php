<?php

namespace Migrations;

use Kantodo\Core\Database\Migration\AbstractMigration;
use Kantodo\Core\Database\Migration\Blueprint;
use Kantodo\Core\Database\Migration\Schema;

class Version_1_0 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        /////////////////
        // TABLE USERS //
        /////////////////
        $schema->create('users', function (Blueprint $table) {
            // columns
            $table->addColumn('user_id', 'bigint', ['unsigned' => true, 'autoincrement' => true]);
            $table->addColumn('firstname', 'varchar', ['length' => 75]);
            $table->addColumn('lastname', 'varchar', ['length' => 75]);
            $table->addColumn('email', 'varchar', ['length' => 200, 'unique' => true]);
            $table->addColumn('password', 'varchar', ['length' => 200]);
            $table->addColumn('secret', 'varchar', ['length' => 50, 'unique' => true]);
            $table->addColumn('nickname', 'varchar', ['length' => 100, 'notNull' => false]);


            $table->addPrimaryKey('user_id');
        });

        /////////////////////
        // TABLE USER_META //
        /////////////////////
        $schema->create('user_meta', function (Blueprint $table, Schema $schema) {
            // columns
            $table->addColumn('meta_id', 'bigint', ['unsigned' => true, 'autoincrement' => true]);
            $table->addColumn('user_id', 'bigint', ['unsigned' => true]);
            $table->addColumn('key', 'varchar', ['length' => 50]);
            $table->addColumn('value', 'text');

            $table->addPrimaryKey('meta_id');

            $table->addForeignKey('user_id', $schema->getTable('users'), 'user_id', $table::ACTION_AFFECT);
        });

        ////////////////////
        // TABLE PROJECTS //
        ////////////////////
        $schema->create('projects', function (Blueprint $table, Schema $schema) {
            $table->addColumn('project_id', 'bigint', ['unsigned' => true, 'autoincrement' => true]);
            $table->addColumn('name', 'varchar');
            $table->addColumn('is_open', 'bool');
            $table->addColumn("uuid", 'varchar', ['length' => 36, 'unique' => true]);
            $table->addColumn('is_public', 'bool');

            $table->addPrimaryKey('project_id');
        });

        /////////////////////////////
        // TABLE PROJECT_POSITIONS //
        /////////////////////////////
        $schema->create('project_positions', function (Blueprint $table) {
            //columns
            $table->addColumn('project_position_id', 'bigint', ['unsigned' => true, 'autoincrement' => true]);
            $table->addColumn('name', 'varchar', ['unique' => true]);

            $table->addPrimaryKey('project_position_id');
        });

        /////////////////////////
        // TABLE USER_PROJECTS //
        /////////////////////////
        $schema->create('user_projects', function (Blueprint $table, Schema $schema) {
            $table->addColumn('user_id', 'bigint', ['unsigned' => true]);
            $table->addColumn('project_id', 'bigint', ['unsigned' => true]);
            $table->addColumn('project_position_id', 'bigint', ['unsigned' => true]);

            $table->addPrimaryKey('user_id');
            $table->addPrimaryKey('project_id');

            $table->addForeignKey('user_id', $schema->getTable('users'), 'user_id', Blueprint::ACTION_AFFECT);
            $table->addForeignKey('project_id', $schema->getTable('projects'), 'project_id', Blueprint::ACTION_AFFECT);
            $table->addForeignKey('project_position_id', $schema->getTable('project_positions'), 'project_position_id', Blueprint::ACTION_AFFECT);

            $table->addUnique(['user_id', 'project_id']);
        });


        /////////////////////////
        // TABLE PROJECT_CODES //
        /////////////////////////
        $schema->create('project_codes', function (Blueprint $table, Schema $schema) {
            $table->addColumn('project_code_id', 'bigint', ['unsigned' => true, 'autoincrement' => true]);
            $table->addColumn('code', 'varchar', ['length' => 50, 'unique' => true]);
            $table->addColumn('expiration', 'datetime', ['notNull' => false]);
            $table->addColumn('project_id', 'bigint', ['unsigned' => true, 'unique' => true]);

            $table->addPrimaryKey('project_code_id');

            $table->addForeignKey('project_id', $schema->getTable('projects'), 'project_id', Blueprint::ACTION_AFFECT);
        });

        /////////////////
        // TABLE TASKS //
        /////////////////
        $schema->create('tasks', function (Blueprint $table, Schema $schema) {
            $table->addColumn('task_id', 'bigint', ['unsigned' => true, 'autoincrement' => true]);
            $table->addColumn('name', 'varchar', ['length' => 150]);
            $table->addColumn('description', 'text', ['length' => 750, 'notNull' => false]);
            $table->addColumn('priority', 'tinyint', ['unsigned' => true]);
            $table->addColumn('completed', 'bool');
            $table->addColumn('end_date', 'datetime', ['notNull' => false]);
            $table->addColumn('creator_id', 'bigint', ['unsigned' => true]);
            $table->addColumn('project_id', 'bigint', ['unsigned' => true]);

            $table->addPrimaryKey('task_id');

            $table->addForeignKey('creator_id', $schema->getTable('users'), 'user_id', Blueprint::ACTION_AFFECT);
            $table->addForeignKey('project_id', $schema->getTable('projects'), 'project_id', Blueprint::ACTION_AFFECT);
        });

        ////////////////
        // TABLE TAGS //
        ////////////////
        $schema->create('tags', function (Blueprint $table) {
            $table->addColumn('tag_id', 'bigint', ['unsigned' => true, 'autoincrement' => true]);
            $table->addColumn('name', 'varchar', ['length' => 50, 'unique' => true]);
            $table->addPrimaryKey('tag_id');
        });

        /////////////////////
        // TABLE TASK_TAGS //
        /////////////////////
        $schema->create('task_tags', function (Blueprint $table, Schema $schema) {
            $table->addColumn('task_id', 'bigint', ['unsigned' => true]);
            $table->addColumn('tag_id', 'bigint', ['unsigned' => true]);

            $table->addPrimaryKey('task_id');
            $table->addPrimaryKey('tag_id');

            $table->addForeignKey('task_id', $schema->getTable('tasks'), 'task_id', Blueprint::ACTION_AFFECT);
            $table->addForeignKey('tag_id', $schema->getTable('tags'), 'tag_id', Blueprint::ACTION_AFFECT);
        });

        ////////////////////////
        // TABLE TAG_PROJECTS //
        ////////////////////////
        $schema->create('tag_projects',  function (Blueprint $table, Schema $schema) {
            $table->addColumn('tag_id', 'bigint', ['unsigned' => true]);
            $table->addColumn('project_id', 'bigint', ['unsigned' => true]);

            $table->addPrimaryKey('tag_id');
            $table->addPrimaryKey('project_id');

            $table->addForeignKey('tag_id', $schema->getTable('tags'), 'tag_id', Blueprint::ACTION_AFFECT);
            $table->addForeignKey('project_id', $schema->getTable('projects'), 'project_id', Blueprint::ACTION_AFFECT);
        });
    }

    public function down(Schema $schema)
    {
        //drop all tables
        $tables = $schema->getTablesNames();

        $tables = array_reverse($tables);
        foreach ($tables as $table) {
            $schema->drop($table);
        }
    }
}
