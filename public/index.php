<?php
/**
 * We need the App file to launch the application
 */

define('ROOT', dirname(__DIR__));
require_once(ROOT . '/core/App.php');

/**
 * Let's Go !
 */

App::get()->run();