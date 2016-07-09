<?php
/**
 * Session class
 * ------------------------------------
 * Session manager
 *
 */

namespace Core\Session;

class Session{

	public static function push($key, $value){
		$_SESSION[$key] = $value;
	}

	public static function pull($key){
		if(isset($_SESSION[$key])){
			return $_SESSION[$key];
		}
	}

	public static function destroy($key){
		if(isset($_SESSION[$key])){
			unset($_SESSION[$key]);
		}
	}

	public static function flash($key){
		if(isset($_SESSION[$key])){
			echo $_SESSION[$key];
			unset($_SESSION[$key]);
		}
	}

	public static function exist($key){
		if(isset($_SESSION[$key])){
			return true;
		}
		return false;
	}
}