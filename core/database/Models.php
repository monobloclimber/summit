<?php 

namespace Core\Database;

/**
 * Parent model for some simplified queries on the database
 */
class Models{

	protected $db;

	public function __construct(\Core\Database\Database $db){
		$this->db = $db;
	}

	public function query($statement, $attributes = null, $one = false){
		if($attributes){
			return $this->db->prepare($statement, $attributes, $one);
		}else{
			return $this->db->query($statement, $one);
		}
	}

	public function find($params){
		return $this->query('SELECT * FROM ' . $this->table . ' WHERE ' . key($params) . ' = ?', [$params[key($params)]], true);
	}

	public function all(){
		$req = $this->query('SELECT * FROM ' . $this->table);
		return $req;
	}

	public function transaction(){
		$this->db->transaction();
		ob_start();
	}

	public function commit(){
		ob_end_flush();
		$this->db->commit();
	}
}