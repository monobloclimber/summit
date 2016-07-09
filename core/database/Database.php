<?php
/**
 * Database class
 * ------------------------------------
 * Manages the connection to the database and can perform basic queries
 *
 */

namespace Core\Database;
use \PDO;

class Database{

	private $db_name;
	private $db_user;
	private $db_pass;
	private $db_host;
	private $fetch_mode;
	private $charset;
	private $pdo;

	public function __construct($config){
		$this->fetch_mode = $config['fetch_mode'];
		$this->charset    = $config['connections']['charset'];
		$this->db_name    = $config['connections']['db_name'];
		$this->db_user    = $config['connections']['user'];
		$this->db_pass    = $config['connections']['pass'];
		$this->db_host    = $config['connections']['host'];
	}

	private function getPDO(){
		if($this->pdo === null){
			$pdo = new PDO('mysql:charset=' . $this->charset . ';dbname=' . $this->db_name .';host=' . $this->db_host, $this->db_user, $this->db_pass);
			$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			$this->pdo = $pdo;
		}

		return $this->pdo;
	}

	public function query($statement, $one = false){
		$req = $this->getPDO()->query($statement);
		if($one){
			$data = $req->fetch($this->fetch_mode);
		}else{
			$data = $req->fetchAll($this->fetch_mode);
		}

		return $data;
	}

	public function prepare($statement, $attributes, $one = false){
		$req = $this->getPdo()->prepare($statement);
		$req->execute($attributes);
		if($one){
			$data = $req->fetch($this->fetch_mode);
		}else{
			$data = $req->fetchAll($this->fetch_mode);
		}

		return $data;
	}

	public function insert($statement, $attributes, $getId = false){
		$req = $this->getPdo()->prepare($statement);
		$result = $req->execute($attributes);

		if($getId){
			return $this->getPdo()->lastInsertId();
		}else{
			return $result;
		}
	}

	public function executeBind($statement, $attributes, $fetch = null, $one = null){
		$req = $this->getPdo()->prepare($statement);

		if($attributes){
			foreach ($attributes as $bind) {
				foreach ($bind as $key => $value) {
					$req->bindValue($key, $value);
				}
			}
		}

		$result = $req->execute();

		if(is_null($fetch)){	
			return $result;
		}elseif($one){
			return $req->fetch($this->fetch_mode);
		}else{
			return $req->fetchAll($this->fetch_mode);
		}
	}

	public function transaction(){
		$this->getPdo()->beginTransaction();
	}

	public function commit(){
		$this->getPdo()->commit();
	}
}