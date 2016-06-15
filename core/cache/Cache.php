<?php 
/**
 * Cache class
 * ------------------------------------ 
 * Allows to put variables or elements in cache
 * 
 */

namespace Core\Cache;

class Cache {
	public $dirname;
	private $duration; # cache duration in minutes
	private $buffer;

	public function __construct($duration, $dirname = null){
		if(is_null($dirname)){
			$this->dirname = ROOT . '/app/' . trim(\Config::get()->read('cache'), '/') . '/';
		}else{
			$this->dirname = ROOT . '/app/' . $dirname;
		}
		$this->duration = $duration;
	}

	public function write($filename, $content){
		return file_put_contents($this->dirname . '/' . $filename, $content);
	}

	public function read($filename){
		$file = $this->dirname . '/' . $filename;
		if(!file_exists($file)){
			return false;
		}
		$lifetime = (time() - filemtime($file)) / 60;
		if($lifetime > $this->duration){
			return false;
		}
		return file_get_contents($this->dirname . '/' . $filename);
	}

	public function delete($filename){
		$file = $this->dirname . '/' . $filename;

		if(file_exists($file)){
			unlink($file);
		}
	}

	public function clear(){
		array_map('unlink', glob($this->dirname . '/*'));
	}

	public function inc($file, $cachename = null){
		if(!$cachename){
			$cachename = basename($file);
		}
		if($content = $this->read($cachename)){
			echo $content;
			return true;
		}
		ob_start();
		require $file;
		$content = ob_get_clean();
		$this->write($cachename, $content);
		echo $content;
		return true;
	}

	public function start($cachename){
		if($content = $this->read($cachename)){
			$this->buffer = false;
			echo $content;
			return true;
		}
		ob_start();
		$this->buffer = $cachename;
	}

	public function end(){
		if($this->buffer){
			$content = ob_get_clean();
			echo $content;
			$this->write($this->buffer, $content);
		}
	}
}