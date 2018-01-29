<?php

class Model_Comments extends Orm\Model
{

    protected static $_table_name = 'comments'; 
    protected static $_properties = array('id',
        'title' => array(
            'data_type' => 'int'
        ), 
        'description' => array(
            'data_type' => 'String'
        ),
        'date' => array(
            'data_type' => 'String'
        ),
        'id_event' => array(
            'data_type' => 'int'
        ),
        'id_user' => array(
            'data_type' => 'float'
        ),
    );

    protected static $_belongs_to = array(
    'users' => array(
        'key_from' => 'id_user',
        'model_to' => 'Model_Users',
        'key_to' => 'id',
        'cascade_save' => true,
        'cascade_delete' => true,
    ),
    'events' => array(
        'key_from' => 'id_event',
        'model_to' => 'Model_Events',
        'key_to' => 'id',
        'cascade_save' => true,
        'cascade_delete' => true,
    )
    );

}