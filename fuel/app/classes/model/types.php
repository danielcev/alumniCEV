<?php

class Model_Types extends Orm\Model
{

    protected static $_table_name = 'types'; 
    protected static $_properties = array('id',
        'name' => array(
            'data_type' => 'varchar'
        ), 

    );

}