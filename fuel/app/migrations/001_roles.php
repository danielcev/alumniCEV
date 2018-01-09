<?php
namespace Fuel\Migrations;

class Roles
{

    function up()
    {
        \DBUtil::create_table('roles', array(
            'id' => array('type' => 'int', 'constraint' => 5, 'auto_increment' => true),
            'type' => array('type' => 'varchar', 'constraint' => 100),

        ), array('id'));

        \DB::query("ALTER TABLE `roles` ADD UNIQUE (`type`)")->execute();
    }

    function down()
    {
       \DBUtil::drop_table('roles');
    }
}