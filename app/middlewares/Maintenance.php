<?php

namespace App\Middleware;

class Maintenance{
	public function execute(){
		if(\Config::get()->read('maintenance')){
			die("The site is down for maintenance thank you to come back later.");
		}
	}
}