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

            //keys
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

            //keys
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

            //keys
            $table->addPrimaryKey('project_id');
        });

        /////////////////////////////
        // TABLE PROJECT_POSITIONS //
        /////////////////////////////
        $schema->create('project_positions', function (Blueprint $table) {
            //columns
            $table->addColumn('project_position_id', 'bigint', ['unsigned' => true, 'autoincrement' => true]);
            $table->addColumn('name', 'varchar', ['unique' => true]);

            //keys
            $table->addPrimaryKey('project_position_id');
        });

        ///////////////////////////////
        // TABLE USER_TEAM_PROJECTS //
        //////////////////////////////
        $schema->create('user_projects', function (Blueprint $table, Schema $schema) {
            $table->addColumn('user_id', 'bigint', ['unsigned' => true]);
            $table->addColumn('project_id', 'bigint', ['unsigned' => true]);
            $table->addColumn('project_position_id', 'bigint', ['unsigned' => true]);

            //keys
            $table->addPrimaryKey('user_id');
            $table->addPrimaryKey('project_id');

            $table->addForeignKey('user_id', $schema->getTable('users'), 'user_id', Blueprint::ACTION_AFFECT);
            $table->addForeignKey('project_id', $schema->getTable('projects'), 'project_id', Blueprint::ACTION_AFFECT);
            $table->addForeignKey('project_position_id', $schema->getTable('project_positions'), 'project_position_id', Blueprint::ACTION_AFFECT);

            $table->addUnique(['user_id', 'project_id']);
        });

        ///////////////////////
        // TABLE MILESTIONES //
        ///////////////////////
        $schema->create('milestones', function (Blueprint $table, Schema $schema) {
            $table->addColumn('milestone_id', 'bigint', ['unsigned' => true, 'autoincrement' => true]);
            $table->addColumn('name', 'varchar', ['length' => 150]);
            $table->addColumn('description', 'text', ['length' => 500, 'notNull' => false]);
            $table->addColumn('end_date', 'datetime', ['notNull' => false]);
            $table->addColumn('project_id', 'bigint', ['unsigned' => true]);

            //keys
            $table->addPrimaryKey('milestone_id');

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
            $table->addColumn('milestone_id', 'bigint', ['unsigned' => true, 'notNull' => false]);
            $table->addColumn('project_id', 'bigint', ['unsigned' => true]);

            //keys
            $table->addPrimaryKey('task_id');

            $table->addForeignKey('creator_id', $schema->getTable('users'), 'user_id', Blueprint::ACTION_AFFECT);
            $table->addForeignKey('milestone_id', $schema->getTable('milestones'), 'milestone_id', Blueprint::ACTION_AFFECT);
            $table->addForeignKey('project_id', $schema->getTable('projects'), 'project_id', Blueprint::ACTION_AFFECT);
        });

        //////////////////////
        // TABLE USER_TASKS //
        //////////////////////
        $schema->create('user_tasks', function (Blueprint $table, Schema $schema) {
            $table->addColumn('user_id', 'bigint', ['unsigned' => true]);
            $table->addColumn('task_id', 'bigint', ['unsigned' => true]);

            $table->addPrimaryKey('user_id');
            $table->addPrimaryKey('task_id');

            $table->addForeignKey('user_id', $schema->getTable('users'), 'user_id', Blueprint::ACTION_AFFECT);
            $table->addForeignKey('task_id', $schema->getTable('tasks'), 'task_id', Blueprint::ACTION_AFFECT);
        });

        /////////////////////////
        // TABLE TASK_COMMENTS //
        /////////////////////////
        $schema->create('task_comments', function (Blueprint $table, Schema $schema) {
            $table->addColumn('task_comment_id', 'bigint', ['unsigned' => true, 'autoincrement' => true]);
            $table->addColumn('content', 'varchar');
            $table->addColumn('edit_date', 'datetime');
            $table->addColumn('parent_comment_id', 'bigint', ['unsigned' => true, 'notNull' => false]);
            $table->addColumn('user_id', 'bigint', ['unsigned' => true]);
            $table->addColumn('task_id', 'bigint', ['unsigned' => true]);

            //keys
            $table->addPrimaryKey('task_comment_id');

            $table->addForeignKey('parent_comment_id', $table, 'task_comment_id', Blueprint::ACTION_NON);
            $table->addForeignKey('user_id', $schema->getTable('users'), 'user_id', Blueprint::ACTION_AFFECT);
            $table->addForeignKey('task_id', $schema->getTable('tasks'), 'task_id', Blueprint::ACTION_AFFECT);
        });

        ////////////////
        // TABLE TAGS //
        ////////////////
        $schema->create('tags', function (Blueprint $table) {
            $table->addColumn('tag_id', 'bigint', ['unsigned' => true, 'autoincrement' => true]);
            $table->addColumn('name', 'varchar', ['length' => 50, 'unique' => true]);
            $table->addColumn('description', 'varchar', ['length' => 150, 'notNull' => false]);

            //keys
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

        ////////////////////////////
        // TABLE TASK_ATTACHMENTS //
        ////////////////////////////
        $schema->create('task_attachments', function (Blueprint $table, Schema $schema) {
            $table->addColumn('task_attachment_id', 'bigint', ['unsigned' => true, 'autoincrement' => true]);
            $table->addColumn('path', 'varchar');
            $table->addColumn('task_id', 'bigint', ['unsigned' => true]);

            //keys
            $table->addPrimaryKey('task_attachment_id');

            $table->addForeignKey('task_id', $schema->getTable('tasks'), 'task_id', Blueprint::ACTION_AFFECT);
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
