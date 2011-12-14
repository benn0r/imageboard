<?php

/**
 * Model
 * 
 * Basis für alle Models
 * 
 * @author benn0r <benjamin@benn0r.ch>
 * @since 13112011
 * @version 13112011
 */
abstract class Model
{
	
	protected $_db;
	
	/**
	 * Tablename
	 * 
	 * @var string
	 */
	protected $_table = '';
		
	public function __construct(Database $db = null) {
		$this->_db = $db == null ? Model::$db : $db;
	}
	
	abstract public function find($id);
	
	protected function getDb() {
		return $this->_db;
	}
	
	public function insert(array $data) {
		return $this->_db->insert($this->_table, $data);
	}
	
	public function update(array $data, $where) {
		return $this->_db->update($this->_table, $data, $where);
	}
	
	static public $db = null;
	
	static public function setDefaultDb(Database $db) {
		self::$db = $db;
	}

}