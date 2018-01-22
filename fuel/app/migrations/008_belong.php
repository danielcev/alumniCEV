<?php
namespace Fuel\Migrations;

class Belong
{

    function up()
    {
        \DBUtil::create_table('belong', array(
            'id_user' => array('type' => 'int', 'constraint' => 5),
            'id_group' => array('type' => 'int', 'constraint' => 5),
        ), array('id_user','id_group'),
            true,
            'InnoDB',
            'utf8_unicode_ci',
            array(
                array(
                    'constraint' => 'claveAjenabelongAUsers',
                    'key' => 'id_user',
                    'reference' => array(
                        'table' => 'users',
                        'column' => 'id',
                    ),
                    'on_update' => 'CASCADE',
                    'on_delete' => 'RESTRICT'
                ),
                array(
                    'constraint' => 'claveAjenabelongAEvents',
                    'key' => 'id_group',
                    'reference' => array(
                        'table' => 'groups',
                        'column' => 'id',
                    ),
                    'on_update' => 'CASCADE',
                    'on_delete' => 'RESTRICT'
                )
            ));
        
    }

    function down()
    {
       \DBUtil::drop_table('belong');
    }
}