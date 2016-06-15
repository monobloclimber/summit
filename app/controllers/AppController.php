<?php

namespace App\Controller;

use \Core\Controller\Controller;

class AppController extends Controller{
	public function __construct(){
		parent::__construct();
		$this->render->defineLayout('views/layout/master.php');
	}
}