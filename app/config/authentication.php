<?php
/**
 * Authentication configuration variables
 * 
 */

return [
	'User' => [ # key to identify the user
		'table'    => 'user', # user table of database
		'id'       => 'id', # field of database for the user id (generally 'id')
		'login'    => 'email', # field of database for the login (generally 'email')
		'password' => 'password', # field of database for the password (generally 'password')
	],
];