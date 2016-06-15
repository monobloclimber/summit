<?php
/**
 * Application routes
 * 
 */

$router->get('/', function(){ echo "Welcome to Summit Framework !";}, 'home');
$router->get('/example', "ExampleController@index");