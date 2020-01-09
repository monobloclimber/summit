<?php
/**
 * App class
 * ------------------------------------ 
 * Load the necessary elements for the application
 * 
 */

use \Core\Authentication\Authentication;
use \Core\Database\QueryBuilder;
use \App\Middlewares\Middleware;
use \Core\Database\Database;
use \Core\Router\Router;
use \Core\Config\Config;

class App{

	private static $_instance;
	private $db_instance;
	private $auth_instance;
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
		require_once(ROOT.'/vendor/autoload.php');
		
		define('DEBUG', Config::get()->read('debug'));
		if(!DEBUG){
			error_reporting(0);
		}else{
			ini_set('display_errors','on');
			error_reporting(E_ALL);
		}
		
		ini_set('session.cookie_lifetime', (60 * Config::get()->read('session')));
		ini_set('session.gc_maxlifetime', (60 * Config::get()->read('session')));
		session_start();
		
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

	/**
	 * Created or retrieve an instance of authentication class
	 * @return object an instance
	 */
	public function Auth(){
		$authentication = require(ROOT.'/app/config/authentication.php');
		if($authentication){
			if(is_null($this->auth_instance)){
				$this->auth_instance = new Authentication($authentication);
			}
			
			return $this->auth_instance;
		}
	}

	/**
	 * Instantiate a QueryBuilder class
	 * @return object a class instance
	 */
	public function QueryBuilder(){
		return new QueryBuilder();
	}
}