<?php 

namespace Core\Controller;

use \Core\Template\Template;
use \Core\Database\QueryBuilder;
use \Core\Cache\Cache;

/**
 * Parent Class controller initialize several things
 */
class Controller{
	public function __construct(){
		$this->render = new Template;
	}

	protected function loadModel($model){
		$this->$model = \App::get()->getTable($model);
	}

	protected function query(){
		return new QueryBuilder();
	}

	protected function cache($duration, $dirname = null){
		return new Cache($duration, $dirname);
	}

	public function notFound(){
		header('HTTP/1.0 404 Not Found');
		$this->render->make('errors.404');
		die();
	}

	public function auth(){
		return \App::get()->auth();
	}
}