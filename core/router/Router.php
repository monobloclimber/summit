<?php
/**
 * Router class
 * ------------------------------------ 
 * The link between the route and the action
 * 
 */

namespace Core\Router;

use \Core\Controller\Controller;

class Router{

	private $url;
	private $routes = [];
	private $namedRoutes = [];

	public function __construct($url){
		$this->url = trim($url, '/');
		unset($_GET['url']);
	}

	public function get($path, $callable, $name = null, $middleware = null){
		return $this->add($path, $callable, $name, 'GET', $middleware);
	}

	public function post($path, $callable, $name = null, $middleware = null){
		return $this->add($path, $callable, $name, 'POST', $middleware);
	}

	public function group($attributes = [], $children = []){
		$prefix = null;
		if(array_key_exists('prefix', $attributes)){
			$prefix = $attributes['prefix'];
		}

		$middleware = null;
		if(array_key_exists('middleware', $attributes)){
			$middleware = $attributes['middleware'];
		}

		foreach ($children as $key => $value) {
			if(is_array($value[0])){
				foreach ($value as $value) {
					$this->groupParse($key, $prefix, $value, $middleware);
				}
			}else{
				$this->groupParse($key, $prefix, $value, $middleware);
			}
		}
	}

	private function groupParse($key, $prefix, $value, $middleware){
		if($prefix){
			$path = $prefix.$value[0];
		}else{
			$path = $value[0];
		}

		$name = null;
		if(isset($value[2])){
			$name = $value[2];
		}

		if(!$middleware && isset($value['3'])){
			$middleware = $value['3'];
		}

		$this->$key($path, $value[1], $name, $middleware);
	}

	public function add($path, $callable, $name, $method, $middleware){
		$route = new Route($path, $callable, $middleware);
		$this->routes[$method][] = $route;

		if($name){
			$this->namedRoutes[$name] = $route;
		}

		return $route;
	}

	public function run(){
		if(!isset($this->routes[$_SERVER['REQUEST_METHOD']])){
			ifNoDebug404();
			error404();
			throw new \Exception('Request method does not exist');
		}
		
		foreach ($this->routes[$_SERVER['REQUEST_METHOD']] as $route){
			if($route->match($this->url)){
				return $route->call();
			}
		}
		
		ifNoDebug404();
		error404();
		throw new \Exception('No route matches');
	}

	public function url($name, $params = []){
		if(!isset($this->namedRoutes[$name])){
			ifNoDebug404();
			error404();
			throw new \Exception('No route matches this name');
		}
		return $this->namedRoutes[$name]->getUrl($params);
	}
}