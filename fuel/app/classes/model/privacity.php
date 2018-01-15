<?php

class Model_Privacity extends Orm\Model
{

   	protected static $_table_name = 'privacity'; 
	protected static $_properties = array('id','phone', 'localization');

	protected static $_belongs_to = array(
    'user' => array(
        'key_from' => 'id_privacity',
        'model_to' => 'Model_User',
        'key_to' => 'id',
        'cascade_save' => true,
        'cascade_delete' => false,
    )
);

}