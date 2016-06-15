<?php 
/**
 * QueryBuilder class
 * ------------------------------------ 
 * The Query Builder provides an interface to most queries on the database
 * 
 */

namespace Core\Database;

use App;

class QueryBuilder{

	private $fields    = ['*'];
	private $where     = [];
	private $orWhere   = [];
	private $whereIn   = [];
	private $table     = [];
	private $skip      = 0;
	private $take      = 0;
	private $group     = [];
	private $order     = [];
	private $join      = [];
	private $leftJoin  = [];
	private $rightJoin = [];
	private $getId     = false;

	public function select(){
		$this->fields = func_get_args();
		return $this;
	}

	public function where(){	
		$this->where[] = func_get_args();
		return $this;
	}

	public function orWhere(){
		$this->orWhere[] = func_get_args();
		return $this;
	}

	public function whereIn(){
		$this->whereIn[] = func_get_args();
		return $this;
	}

	public function table($table, $alias = null){
		if(is_null($alias)){
			$this->table[] = $table;
		}else{
			$this->table[] = "$table AS $alias";
		}
		return $this;
	}

	public function skip($skip){
		$this->skip = $skip;
		return $this;
	}

	public function take($take){
		$this->take = $take;
		return $this;
	}

	public function groupBy(){
		foreach (func_get_args() as $arg) {
			$this->group[] = $arg;
		}
		return $this;
	}

	public function orderBy(){
		foreach (func_get_args() as $arg) {
			$this->order[] = $arg;
		}
		return $this;
	}

	public function join(){
		$this->join[] = func_get_args();
		return $this;
	}

	public function leftJoin(){
		$this->leftJoin[] = func_get_args();
		return $this;
	}

	public function rightJoin(){
		$this->rightJoin[] = func_get_args();
		return $this;
	}

	public function builder(){
		$values = null;
		$return = 'SELECT ' . implode(', ', $this->fields) . ' FROM ' . implode(', ', $this->table);

		if($this->join){
			foreach ($this->join as $join) {
				$return .= ' INNER JOIN ' . $join[0] . ' ON ' . $join[1] . ' = ' . $join[2];
			}
		}

		if($this->leftJoin){
			foreach ($this->leftJoin as $join) {
				$return .= ' LEFT JOIN ' . $join[0] . ' ON ' . $join[1] . ' = ' . $join[2];
			}
		}

		if($this->rightJoin){
			foreach ($this->rightJoin as $join) {
				$return .= ' RIGHT JOIN ' . $join[0] . ' ON ' . $join[1] . ' = ' . $join[2];
			}
		}
		
		if($this->where || $this->whereIn){
			$treatment = $this->preprocessingOnWhere($return);
			$return = $treatment[0];
			$values = $treatment[1];
		}

		if($this->group){
			$return .= ' GROUP BY ' . implode(', ', $this->group);
		}

		if($this->order){
			$return .= ' ORDER BY ' . implode(', ', $this->order);
		}

		if($this->take){
			$return .= ' LIMIT ' . $this->skip . ', ' . $this->take;
		}

		return [$return, $values];
	}

	public function get(){
		$result = $this->builder();
		return \App::get()->getDB()->executeBind($result[0], $result[1], true);
	}

	public function first(){
		$this->skip = 0;
		$this->take = 1;

		$result = $this->builder();
		return \App::get()->getDB()->executeBind($result[0], $result[1], true, true);
	}

	public function count(){
		$this->fields = ['COUNT(*) as aggregate'];
		return $this->first()->aggregate;
	}

	public function insert(){
		$args = func_get_args();
		foreach ($args[0] as $key => $value) {
			$keys[] = $key;
			$refs[] = '?';
			$values[] = $value;
		}
		$columns = implode(', ', $keys);
		$references = implode(', ', $refs);

		$req = 'INSERT INTO ' . $this->table[0] . ' (' . $columns . ') VALUES(' . $references . ')';
		return \App::get()->getDB()->insert($req, $values, $this->getId);
	}

	public function insertGetId($args){
		$this->getId = true;
		return $this->insert($args);
	}

	public function update(){
		$args = func_get_args();
		foreach ($args[0] as $key => $value) {
			$ref = uniqid(':' . $key);
			$sets[] = $key . ' = ' . $ref;
			$values[] = [$ref => $value];
		}
		$columns = implode(', ', $sets);

		$req = 'UPDATE ' . $this->table[0] . ' SET ' . $columns;

		$return = $this->preprocessingOnWhere($req);
		$req = $return[0];
		$values = array_merge($values, $return[1]);
		
		return \App::get()->getDB()->executeBind($req, $values);
	}

	public function delete(){
		$req = 'DELETE FROM ' . $this->table[0];

		$return = $this->preprocessingOnWhere($req);
		$req = $return[0];
		$values = $return[1];

		return \App::get()->getDB()->executeBind($req, $values);
	}

	private function preprocessingOnWhere($req){
		if($this->where){
			foreach ($this->where as $where) {
				$column = $where[0];
				$ref = uniqid(':'.$column);
				$sign = $where[1];
				$value = $where[2];
				$whereRefs[] = $column . ' ' . $sign . ' ' . $ref;
				$values[] = [$ref => $value];
			}
			$whereColumns = implode(' AND ', $whereRefs);
			$req .= ' WHERE ' . $whereColumns;
		}

		if($this->whereIn){
			if($this->where){
				$condition = 'AND';
			}else{
				$condition = 'WHERE';
			}
			foreach ($this->whereIn as $key => $v) {
				foreach ($v[1] as $key => $result) {
					$ref = uniqid(':');
					$values[] = [$ref => $result];
					$in[] = $ref;
				}
				$req .= ' ' . $condition . ' '. $v[0] . ' IN ' . '(' . implode(', ', $in) . ')';
				unset($in);
				$condition = 'AND';
			}
		}

		if($this->orWhere){
			foreach ($this->orWhere as $where) {
				$column = $where[0];
				$ref = uniqid(':'.$column);
				$sign = $where[1];
				$value = $where[2];
				$orWhereRefs[] = $column . ' ' . $sign . ' ' . $ref;
				$values[] = [$ref => $value];
			}
			$orWhereColumns = implode(' OR ', $orWhereRefs);
			$req .= ' OR ' . $orWhereColumns;
		}

		return [$req, $values];
	}
}