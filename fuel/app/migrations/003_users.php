<?php
namespace Fuel\Migrations;

class Users
{

    function up()
    {
        \DBUtil::create_table('users', array(
            'id' => array('type' => 'int', 'constraint' => 5, 'auto_increment' => true),
            'email' => array('type' => 'varchar', 'constraint' => 100),
            'password' => array('type' => 'varchar', 'constraint' => 100),
            'phone' => array('type' => 'int', 'constraint' => 9, 'null' => true),
            'username' => array('type' => 'varchar', 'constraint' => 100),
            'birthday' => array('type' => 'varchar', 'constraint' => 100, 'null' => true),
            'is_registered' => array('type' => 'boolean'),
            'id_rol' => array('type' => 'int', 'constraint' => 5),
            'id_privacity' => array('type' => 'int', 'constraint' => 5),
            'group' => array('type' => 'varchar', 'constraint' => 100),
            'description' => array('type' => 'varchar', 'constraint' => 100, 'null' => true),
            'photo' => array('type' => 'varchar', 'constraint' => 100, 'null' => true),
            'name' => array('type' => 'varchar', 'constraint' => 100),
            'lastnames' => array('type' => 'varchar', 'constraint' => 100),
            'x' => array('type' => 'float', 'constraint' => 25, 'null' => true),
            'y' => array('type' => 'float', 'constraint' => 25, 'null' => true),
        ), array('id'),
            true,
            'InnoDB',
            'utf8_unicode_ci',
            array(
                array(
                    'constraint' => 'claveAjenaUsersARoles',
                    'key' => 'id_rol',
                    'reference' => array(
                        'table' => 'roles',
                        'column' => 'id',
                    ),
                    'on_update' => 'CASCADE',
                    'on_delete' => 'RESTRICT'
                ),
                array(
                    'constraint' => 'claveAjenaUsersAPrivacity',
                    'key' => 'id_privacity',
                    'reference' => array(
                        'table' => 'privacity',
                        'column' => 'id',
                    ),
                    'on_update' => 'CASCADE',
                    'on_delete' => 'CASCADE'
                )
            ));

        \DB::query("ALTER TABLE `users` ADD UNIQUE (`email`)")->execute();
        \DB::query("ALTER TABLE `users` ADD UNIQUE (`phone`)")->execute();
        \DB::query("ALTER TABLE `users` ADD UNIQUE (`username`)")->execute();
    }

    function down()
    {
       \DBUtil::drop_table('users');
    }
}