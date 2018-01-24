<?php

class Model_Belong extends Orm\Model
{

    protected static $_table_name = 'comments'; 
    protected static $_properties = array(
        'id_user' => array(
            'data_type' => 'int'),
        'id_group' => array(
            'data_type' => 'int'),

    );

}