<?php 
/**
 * Core Folder structures for the Autoloader
 * ------------------------------------ 
 * Path starting from the "app" folder.
 * Classified by load order.
 *
 * This file is merged with "app/config/paths.php" in the autoloader
 * 
 */

return [
	'../core',
	'../core/config',
	'../core/autoloader',
	'../core/router',
	'../core/cache',
	'../core/database',
	'../core/authentication',
	'../core/controller',
	'../core/session',
	'../core/template',
	'../core/mail',
	'../core/pagination',
];