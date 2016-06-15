<?php
/**
 * Middleware class
 * ------------------------------------ 
 * Defines events to trigger before the request to the application
 * 
 */

namespace App\Middleware;

class Middleware{
	/**
	 * Automatically load
	 * @var array
	 */
	protected $globalMiddleware = [
		'App\Middleware\Maintenance',
	];

	/**
	 * Load from the road if this is defined
	 * @var array
	 */
	public $routeMiddleware = [
		// 'nameOfMiddleware' => 'App\Middleware\ClassName',
	];

	public function load(){
		foreach ($this->globalMiddleware as $middleware) {
			$theMiddleware = new $middleware();
			$theMiddleware->execute();
		}
	}
}