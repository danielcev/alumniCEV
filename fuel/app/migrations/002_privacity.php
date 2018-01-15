<?php
namespace Fuel\Migrations;

class Privacity
{

    function up()
    {
        \DBUtil::create_table('privacity', array(
            'id' => array('type' => 'int', 'constraint' => 5, 'auto_increment' => true),
            'phone' => array('type' => 'bool'),
            'localization' => array('type' => 'bool')

        ), array('id'));
    }

    function down()
    {
       \DBUtil::drop_table('privacity');
    }
}