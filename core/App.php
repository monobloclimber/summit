<?php
/**
 * App class
 * ------------------------------------ 
 * Load the necessary elements for the application
 * 
 */

use \Core\Autoloader\Autoloader;
use \Core\Router\Router;
use \Core\Database\Database;
use \App\Middleware\Middleware;

class App{

	private $db_instance;
	private static $_instance;
	public $router;

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
	public function run(){
		session_start();
		require_once(ROOT.'/core/autoloader/Autoloader.php');
		$paths = require_once(ROOT.'/app/config/paths.php');
		$autoloader = new Autoloader($paths);
		$autoloader->start();
		
		$config = require(ROOT.'/app/config/app.php');
		define('DEBUG', Config::get()->read('debug'));
		if(!DEBUG){error_reporting(0);}
		
		require_once(ROOT.'/core/helpers.php');
		
        $router = new Router(isset($_GET['url']) ? $_GET['url'] : null);
        require_once(ROOT.'/app/routes.php');
        $this->router = $router;
		
		$middleware = new Middleware();
		$middleware->load();
        
        $router->run();
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
}