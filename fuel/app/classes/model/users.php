<?php

class Model_Users extends Orm\Model
{

   	protected static $_table_name = 'users';
	protected static $_properties = array('id','email','password','mobile_phone','ubication','birthday','is_registered','id_rol');

}