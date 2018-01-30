<?php

class Model_Privacity extends Orm\Model
{

   	protected static $_table_name = 'privacity'; 
	protected static $_properties = array('id',
        'phone' => array(
            'data_type' => 'int'
        ), 
        'localization' => array(
            'data_type' => 'int'
        )
    );

	protected static $_belongs_to = array(
    'users' => array(
        'key_from' => 'id',
        'model_to' => 'Model_Users',
        'key_to' => 'id_privacity',
        'cascade_save' => true,
        'cascade_delete' => false,
        )
    );

}