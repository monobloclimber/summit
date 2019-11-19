<?php

define('ROOT', dirname(__DIR__));

include_once 'config/Config.php';
include_once 'database/Database.php';
include_once 'database/Models.php';
include_once 'database/QueryBuilder.php';
include_once 'template/Template.php';
include_once 'mail/Mail.php';

/**
 * Allows you to launch a call on the application from CLI
 * ------------------------------------ 
 * Load the necessary elements for the application
 */

use \Core\Mail\Mail;
use \Core\Database\Database;
use \Core\Database\QueryBuilder;

class App{

	private static $_instance;
	private $db_instance;
	private $auth_instance;
	
	/**
	 * Created or retrieve an instance of the application
	 * @return object an instance
	 */
	public static function get(){
		if(is_null(self::$_instance)){
			self::$_instance = new App();
		}
		return self::$_instance;
	}

	/**
	 * Launches the prerequisites to using the application
	 */
	public function run($argv){
		$config = require(ROOT.'/app/config/app.php');
		require_once(ROOT.'/core/helpers.php');

		if(count($argv) > 2){
			require_once ROOT.'/app/controllers/'.$argv[1].'.php';
			$class = new $argv[1];
			call_user_func([$class, $argv[2]]);
		}
	}	

	/**
	 * Instantiate a model class
	 * @param  string class name
	 * @return object a class instance
	 */
	public function getTable($name){
		$class_name = '\\App\Models\\'. ucfirst($name);
		return new $class_name($this->getDb());
	}

	/**
	 * Created or retrieve an instance of database class
	 * @return object an instance
	 */
	public function getDb(){
		if(Config::get()->read('production')){
			$config = require(ROOT.'/app/config/database.php');
		}else{
			$config = require(ROOT.'/app/config/local/database.php');
		}
		if(is_null($this->db_instance)){
			$this->db_instance = new Database($config);
		}
		
		return $this->db_instance;
	}

	/**
	 * Instantiate a QueryBuilder class
	 * @return object a class instance
	 */
	public function QueryBuilder(){
		return new QueryBuilder();
	}
}

/**
 * Let's Go !
 */

App::get()->run($argv);