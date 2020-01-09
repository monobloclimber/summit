<?php

/**
 * Provides the configuration of the application
 */

namespace Core\Config;

class Config{
	
	private $settings = [];
	private static $_instance;

	public static function get(){
		if(is_null(self::$_instance)){
			self::$_instance = new Config();
		}
		return self::$_instance;
	}
	
	public function __construct(){
		$this->settings = require(ROOT . '/app/config/app.php');
	}

	public function read($key){
		if(!isset($this->settings[$key])){
			return null;
		}
		return $this->settings[$key];
	}
}