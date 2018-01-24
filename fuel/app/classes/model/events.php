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
        'date' => array(
            'data_type' => 'varchar'
        ),
        'id_user' => array(
            'data_type' => 'int'
        ),
        'id_type' => array(
            'data_type' => 'int'
        )
    );

}