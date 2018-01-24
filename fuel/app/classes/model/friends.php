<?php

class Model_Friends extends Orm\Model
{

    protected static $_table_name = 'comments'; 
    protected static $_properties = array(
        'id_user_receive' => array(
            'data_type' => 'int'
        ),
        'id_user_send' => array(
            'data_type' => 'int'
        ),
    );

}