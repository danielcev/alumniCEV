<?php

class Model_Events extends Orm\Model
{

    protected static $_table_name = 'events'; 
    protected static $_properties = array('id',
        'title' => array(
            'data_type' => 'int'
        ), 
        'description' => array(
            'data_type' => 'String'
        ),
        'image' => array(
            'data_type' => 'String'
        ),
        'lat' => array(
            'data_type' => 'float'
        ),
        'lon' => array(
            'data_type' => 'float'
        ),
        'group' => array(
            'data_type' => 'int'
        ),
        'id_user' => array(
            'data_type' => 'int'
        )
    );
/*
    protected static $_belongs_to = array(
    'users' => array(
        'key_from' => 'id_privacity',
        'model_to' => 'Model_Users',
        'key_to' => 'id',
        'cascade_save' => true,
        'cascade_delete' => false,
    )
);*/

}