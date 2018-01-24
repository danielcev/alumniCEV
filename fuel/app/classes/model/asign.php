<?php

class Model_Asign extends Orm\Model
{

    protected static $_table_name = 'comments'; 
    protected static $_properties = array(
        'id_event' => array(
            'data_type' => 'int'),
        'id_group' => array(
            'data_type' => 'int'),

    );

}