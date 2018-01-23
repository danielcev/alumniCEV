<?php
namespace Fuel\Migrations;

class Types
{

    function up()
    {
        \DBUtil::create_table('Types', array(
            'id' => array('type' => 'int', 'constraint' => 5, 'auto_increment' => true),
            'name' => array('type' => 'varchar', 'constraint'=>50),

        ), array('id'));
    }

    function down()
    {
       \DBUtil::drop_table('Types');
    }
}