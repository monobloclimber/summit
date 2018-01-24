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
		
		if(in_array('App', $array)){
			$paths_tmp = $this->paths;
			if(in_array('Models', $array)){
				foreach ($paths_tmp as $key => $path) {
					if(substr($path, 0, 6) != 'models'){
						unset($paths_tmp[$key]);
					}
				}
			}elseif(in_array('Libraries', $array)){
				foreach ($paths_tmp as $key => $path) {
					if(substr($path, 0, 9) != 'libraries'){
						unset($paths_tmp[$key]);
					}
				}
			}
		}

		if(isset($paths_tmp)){
			$buffer = $paths_tmp;
		}else{
			$buffer = $this->paths;
		}

		foreach ($buffer as $path) {
			if(file_exists('../app/' . $path.'/' . $class . '.php')){
				include_once '../app/' . $path.'/' . $class . '.php';
				break;
			}
		}
	}
}