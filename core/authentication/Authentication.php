<?php 
/**
 * Authentication class
 * ------------------------------------ 
 * Authenticates users and manage this connection
 * 
 */

namespace Core\Authentication;

use \Core\Database\QueryBuilder;
use \Core\Session\Session;

class Authentication {

	private $config;

	public function __construct($config){
		$this->config = $config;
	}

	public function login($key, $login, $password){
		$this->checkConfig($key);
		
		$user = $this->exist($key, $login);
		$config = $this->config[$key];

		if($user && password_verify($password, $user->$config['password'])){
			Session::push(
				'authentication',
				[
					'key' => $key,
					'login'  => $user->$config['login']
				]
			);
			return true;
		}

		return false;
	}

	public function logout(){
		Session::destroy('authentication');
	}

	public function check($key = null, $result = null){
		if(Session::exist('authentication')){
			$session = Session::pull('authentication');
			if($key && ($session['key'] != $key)){
				return false;
			}

			$user = $this->exist($session['key'], $session['login']);

			if($user){
				if($result){
					return $user;
				}
				return true;
			}
		}

		return false;
	}

	public function get(){
		return $this->check(null, true);
	}

	private function exist($key, $login){
		$config = $this->config[$key];
		$query = new QueryBuilder();
		$user = $query->table($config['table'])->where($config['login'], '=', $login)->first();
		
		return $user;
	}

	public function loginById($key, $id){
		$this->logout();
		$this->checkConfig($key);

		$config = $this->config[$key];
		$query = new QueryBuilder();
		$user = $query->table($config['table'])->where($config['id'], '=', $id)->first();

		if($user){
			Session::push(
				'authentication',
				[
					'key' => $key,
					'login'  => $user->$config['login']
				]
			);
			return true;
		}

		return false;
	}

	private function checkConfig($key){
		if(!isset($this->config[$key])){
			throw new \Exception('No match for this type of user in app/config/authentication.php');
		}
		if(!isset($this->config[$key]['table'])){
			throw new \Exception('Undefined table in app/config/authentication.php');
		}
		if(!isset($this->config[$key]['id'])){
			throw new \Exception('Undefined id in app/config/authentication.php');
		}
		if(!isset($this->config[$key]['login'])){
			throw new \Exception('Undefined login in app/config/authentication.php');
		}
		if(!isset($this->config[$key]['password'])){
			throw new \Exception('Undefined password in app/config/authentication.php');
		}
	}

	public function who(){
		if($this->check()){
			return Session::pull('authentication')['key'];
		}
		return false;
	}
}