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
	}

	public function get($path, $callable, $name = null, $middleware = null){
		return $this->add($path, $callable, $name, 'GET', $middleware);
	}

    public function post($path, $callable, $name = null, $middleware = null){
        return $this->add($path, $callable, $name, 'POST', $middleware);
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
            throw new \Exception('Request method does not exist');
        }
        
        foreach ($this->routes[$_SERVER['REQUEST_METHOD']] as $route){
            if($route->match($this->url)){
                return $route->call();
            }
        }
        
        ifNoDebug404();
        throw new \Exception('No route matches');
    }

    public function url($name, $params = []){
        if(!isset($this->namedRoutes[$name])){
            ifNoDebug404();
            throw new \Exception('No route matches this name');
        }
        return $this->namedRoutes[$name]->getUrl($params);
    }
}