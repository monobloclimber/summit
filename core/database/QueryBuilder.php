<?php 
/**
 * QueryBuilder class
 * ------------------------------------ 
 * The Query Builder provides an interface to most queries on the database
 * 
 */

namespace Core\Database;

use App;

class QueryBuilder extends Models{

	private $fields    = ['*'];
	private $where     = [];
	private $orWhere   = [];
	private $whereIn   = [];
	private $whereRaw  = [];
	private $from      = [];
	private $skip      = 0;
	private $take      = 0;
	private $group     = [];
	private $order     = [];
	private $join      = [];
	private $leftJoin  = [];
	private $rightJoin = [];
	private $getId     = false;

	public function __construct(\Core\Database\Database $db = null){
		$this->db = $db;

		if(isset($this->table)){
			$this->from  = [$this->table];
		}else{
			$this->from  = [];
		}
	}
	
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

	public function whereRaw(){
		foreach (func_get_args() as $arg) {
			$this->whereRaw[] = $arg;
		}
		return $this;
	}

	public function table($table, $alias = null){
		if(is_null($alias)){
			$this->from[] = $table;
		}else{
			$this->from[] = "$table AS $alias";
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
		$return = 'SELECT ' . implode(', ', $this->fields) . ' FROM ' . implode(', ', $this->from);

		if($this->join){
			foreach ($this->join as $join) {
				if(count($join) == 3){
					$sign = '=';
					$last = $join[2];
				}else{
					$sign = $join[2];
					$last = $join[3];
				}

				$return .= ' INNER JOIN ' . $join[0] . ' ON ' . $join[1] . ' ' . $sign  . ' ' . $last;
			}
		}

		if($this->leftJoin){
			foreach ($this->leftJoin as $join) {
				if(count($join) == 3){
					$sign = '=';
					$last = $join[2];
				}else{
					$sign = $join[2];
					$last = $join[3];
				}

				$return .= ' LEFT JOIN ' . $join[0] . ' ON ' . $join[1] . ' ' . $sign  . ' ' . $last;
			}
		}

		if($this->rightJoin){
			foreach ($this->rightJoin as $join) {
				if(count($join) == 3){
					$sign = '=';
					$last = $join[2];
				}else{
					$sign = $join[2];
					$last = $join[3];
				}

				$return .= ' RIGHT JOIN ' . $join[0] . ' ON ' . $join[1] . ' ' . $sign  . ' ' . $last;
			}
		}
		
		if($this->where || $this->whereIn){
			$treatment = $this->preprocessingOnWhere($return);
			$return = $treatment[0];
			$values = $treatment[1];
		}

		if($this->whereRaw){
			foreach ($this->whereRaw as $where){
				$return .= ' '.$where;
			}
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

	public function count($field = null){
		if($field){
			$this->fields = ['COUNT(' . $field . ') as aggregate'];
		}else{
			$this->fields = ['COUNT(*) as aggregate'];
		}
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

		$req = 'INSERT INTO ' . $this->from[0] . ' (' . $columns . ') VALUES(' . $references . ')';
		return \App::get()->getDB()->insert($req, $values, $this->getId);
	}

	public function insertGetId($args){
		$this->getId = true;
		return $this->insert($args);
	}

	public function update(){
		$args = func_get_args();
		foreach ($args[0] as $key => $value) {
			$ref = uniqid(':');
			$sets[] = $key . ' = ' . $ref;
			$values[] = [$ref => $value];
		}
		$columns = implode(', ', $sets);

		$req = 'UPDATE ' . $this->from[0] . ' SET ' . $columns;

		$return = $this->preprocessingOnWhere($req);
		$req = $return[0];
		$values = array_merge($values, $return[1]);
		
		return \App::get()->getDB()->executeBind($req, $values);
	}

	public function delete(){
		$req = 'DELETE FROM ' . $this->from[0];
		$values = null;
		
		if($this->where || $this->whereIn){
			$return = $this->preprocessingOnWhere($req);
			$req = $return[0];
			$values = $return[1];
		}

		if($this->whereRaw){
			foreach ($this->whereRaw as $where){
				$req .= ' '.$where;
			}
		}

		return \App::get()->getDB()->executeBind($req, $values);
	}

	private function preprocessingOnWhere($req){
		if($this->where){
			foreach ($this->where as $where) {
				$column = $where[0];
				$ref = uniqid(':');
				if(count($where) == 2){
					$sign = '=';
					$value = $where[1];
				}else{
					$sign = $where[1];
					$value = $where[2];
				}
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
				$ref = uniqid(':');
				if(count($where) == 2){
					$sign = '=';
					$value = $where[1];
				}else{
					$sign = $where[1];
					$value = $where[2];
				}
				$orWhereRefs[] = $column . ' ' . $sign . ' ' . $ref;
				$values[] = [$ref => $value];
			}
			$orWhereColumns = implode(' OR ', $orWhereRefs);
			$req .= ' OR ' . $orWhereColumns;
		}

		return [$req, $values];
	}
}