<?php
/**
 * Route class
 * ------------------------------------
 * Performs various tasks on the road
 *
 */

namespace Core\Router;

class Route{

    private $path;
    private $callable;
    private $middleware;
    private $matches = [];
    private $params = [];

    public function __construct($path, $callable, $middleware){
        $this->path = trim($path, '/');
        $this->callable = $callable;
        $this->middleware = $middleware;
    }

    public function with($param, $regex){
    	$this->params[$param] = str_replace('(', '(?:', $regex);
    	return $this;
    }

    public function match($url){
    	$url = trim($url, '/');
    	$path = preg_replace_callback('#{([\w]+)}#', [$this, 'paramMatch'], $this->path);
    	$regex = "#^$path$#i";
    	if(!preg_match($regex, $url, $matches)){
    		return false;
    	}

    	array_shift($matches);
    	$this->matches = $matches;
    	return true;
    }

    private function paramMatch($match){
    	if(isset($this->params[$match[1]])){
    		return '(' . $this->params[$match[1]] . ')';
    	}
    	return '([^/]+)';
    }

    public function call(){
        $globalMiddleware = new \App\Middleware\Middleware();
        $routeMiddleware = $globalMiddleware->routeMiddleware;
        if(!is_null($this->middleware)){
            if(array_key_exists($this->middleware, $routeMiddleware)){
                $theMiddleware = $routeMiddleware[$this->middleware];
                $theMiddleware = new $theMiddleware;
                $theMiddleware->execute();
            }else{
                error404();
                throw new \Exception("The called middleware is unknown");
            }
        }

    	if(is_string($this->callable)){
    		$params = explode('@', $this->callable);
    		$controller = "App\\Controller\\" . $params[0];
    		$controller = new $controller();
            return call_user_func_array([$controller, $params[1]], $this->matches);
    	}else{
    		return call_user_func_array($this->callable, $this->matches);
    	}
    }

    public function getUrl($params){
    	$path = $this->path;
    	foreach ($params as $key => $value) {
    		$path = str_replace('{'.$key.'}', $value, $path);
    	}

    	return $path;
    }
}