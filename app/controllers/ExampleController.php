<?php

namespace App\Controller;

class ExampleController extends AppController{
	public function index(){
		$data['var'] = "You are on a sample page.";
		return $this->render->make('example.index', $data);
	}
}