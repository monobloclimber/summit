<?php
/**
 * Autoloader class
 * ------------------------------------ 
 * Allows the automatic loading of classes
 * 
 */

namespace Core\Autoloader;

class Autoloader{
	public $paths;

	public function __construct($paths){

		$this->paths = array_merge(require(ROOT . '/core/config/paths.php'), $paths);
	}

	public function start(){
		spl_autoload_register(array(__CLASS__, 'load'));
	}

	public function load($class) {
		$array = explode('\\', $class);

		if(count($array) > 1){
			$class = end($array);
		}

		foreach ($this->paths as $path) {
			if(file_exists('../app/' . $path.'/' . $class . '.php')){
				include_once '../app/' . $path.'/' . $class . '.php';
				break;
			}
		}
	}
}