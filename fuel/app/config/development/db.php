<?php
/**
 * The development database settings. These get merged with the global settings.
 */

// LOCAL
/*
return array(
	'default' => array(
		'connection'  => array(
			'dsn'        => 'mysql:host=localhost:8889;dbname=alumniCEV',
			'username'   => 'root',
			'password'   => 'root',
		),
	),
);

*/

// PRODUCCION


return array(
	'default' => array(
		'connection'  => array(
			'dsn'        => 'mysql:host=localhost:3306;dbname=alumniCEV',
			'username'   => 'root',
			'password'   => '',
		),
	),
);

