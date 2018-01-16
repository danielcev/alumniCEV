<?php

class Model_Users extends Orm\Model
{

   	protected static $_table_name = 'users';
	protected static $_properties = array(
        'id', // both validation & typing observers will ignore the PK
        'email' => array(
            'data_type' => 'varchar',
        ),
        'password' => array(
            'data_type' => 'varchar',
        ),
        'phone' => array(
            'data_type' => 'int',
        ),
        'username' => array(
            'data_type' => 'varchar',
        ),
        'birthday' => array(
            'data_type' => 'varchar',
        ),
        'is_registered' => array(
            'data_type' => 'int',
        ),
        'id_rol' => array(
            'data_type' => 'int',
        ),
        'id_privacity' => array(
            'data_type' => 'int',
        ),
        'group' => array(
            'data_type' => 'varchar',
        ),
        'description' => array(
            'data_type' => 'varchar',
        ),
        'photo' => array(
            'data_type' => 'varchar',
        ),
        'name' => array(
            'data_type' => 'varchar',
        ),
        'lon' => array(
            'data_type' => 'varchar',
        ),
        'lat' => array(
            'data_type' => 'varchar',
        ),
    );

    protected static $_has_one = array(
    'roles' => array(
        'key_from' => 'id',
        'model_to' => 'Model_Roles',
        'key_to' => 'id_rol',
        'cascade_save' => true,
        'cascade_delete' => false,
    ),
    'privacity' => array(
        'key_from' => 'id',
        'model_to' => 'Model_Privacity',
        'key_to' => 'id_privacity',
        'cascade_save' => true,
        'cascade_delete' => true,
    )
);

}