<?php

/**
 * Request
 * 
 * Ersatz für die superglobalen von PHP
 * 
 * @author benn0r <benjamin@benn0r.ch>
 * @since 29102011
 * @version 29102011
 */
class Database
{
	
	private $_mysqli = null;
	
	protected $_host = null;
	protected $_username = null;
	protected $_password = null;
	protected $_dbname = null;
	
	public function getDbAdapter() {
		return $this->_mysqli;
	}
	
	public function __construct($host, $username, $password, $dbname) {
		$this->_host = $host;
		$this->_username = $username;
		$this->_password = $password;
		$this->_dbname = $dbname;
	}
	
	public function __destruct () {
		if ($this->_mysqli !== null) {
			$this->_mysqli->close();
		}
	}
	
	protected function _connect() {
		$mysqli = new mysqli($this->_host, $this->_username, $this->_password, $this->_dbname);
		if ($mysqli->connect_error) {
			throw new Database_Exception($mysqli->connect_error);
		}
		$this->_mysqli = $mysqli;
	}
	
	/**
	 * Fuert ein UPDATE-Statement fuer die Tabelle $table durch.
	 * 
	 * @param $table Tabelle
	 * @param $data Felder
	 * @param $where Where-Klausel
	 * @return ressource
	 */
	public function update ($table, $data, $where = '') {

		if ($this->_mysqli === null) {
			// Datenbankverbindung herstellen
			$this->_connect();
		}
		
		// $table muss ein String sein
		if (!is_string($table)) {
			trigger_error('$table must be a string', E_USER_ERROR);
		}
		// $data muss ein array sein
		if (!is_array($data)) {
			trigger_error('$data must be an array', E_USER_ERROR);
		}
		
		// Tabelle
		$query = 'UPDATE `' . $table . '` SET ';
		
		// Felder
		$fields = array();
		foreach ($data as $name => $value) {
			$fields[] = '`' . $name . '` = ' . $this->enclose($value);
		}
		$query .= implode(', ', $fields);
		
		// Where
		if ($where) {
			$query .= ' WHERE ' . $where;
		}
				
		// Query ausfuehren
		if (!$this->_mysqli->query($query)) {
			throw new Database_Exception($this->_mysqli->error);
		}
		
	}
	
	/**
	 * Fuert ein INSERT-Statement fuer die Tabelle $table durch.
	 * 
	 * @param $table Tabelle
	 * @param $data Felder
	 * @return ressource
	 */
	public function insert ($table, $data) {
		
		if ($this->_mysqli === null) {
			// Datenbankverbindung herstellen
			$this->_connect();
		}
		
		// $table muss ein String sein
		if (!is_string($table)) {
			trigger_error('$table must be a string', E_USER_ERROR);
		}
		// $data muss ein array sein
		if (!is_array($data)) {
			trigger_error('$data must be an array', E_USER_ERROR);
		}
		
		// Tabelle
		$query = 'INSERT INTO `' . $table . '` SET ';
		
		// Felder
		$fields = array();
		foreach ($data as $name => $value) {
			$fields[] = '`' . $name . '` = ' . $this->enclose($value);
		}
		$query .= implode(', ', $fields);
		
		// Query ausfuehren
		if (!$result = $this->_mysqli->query($query)) {
			throw new Database_Exception($this->_mysqli->error);
		}
		return $result;
		
	}
	
	public function select ($sql) {
		return $this->exec($sql);
	}
	
	public function exec ($sql, $return = true) {
		if ($this->_mysqli === null) {
			// Datenbankverbindung herstellen
			$this->_connect();
		}
		
		if (!$result = $this->_mysqli->query($sql)) {
			throw new Database_Exception($this->_mysqli->error);
		}
		return $result;
	}
	
	public function lastInsertId () {
		if ($this->_mysqli === null) {
			return null;
		}
		return $this->_mysqli->insert_id;
	}
	
	/**
	 * Gibt ein Element zurueck das gefahrlos in einem
	 * Mysql-Query verwendet werden kann
	 * 
	 * @param $value string
	 * @param $escape bool Gibt an ob strings escaped werden
	 * @return mixed
	 */
	public function enclose ($value, $escape = true) {
		if ($this->_mysqli === null) {
			// Datenbankverbindung herstellen
			$this->_connect();
		}
		
		switch (gettype($value)) {
			case 'string':
			default:
				if ($escape) {
					return '"' . $this->_mysqli->real_escape_string($value) . '"';
				} else {
					return '"' . $value . '"';
				}
			case 'integer':
			case 'double':
				return $this->_mysqli->real_escape_string($value);
			case 'object':
				if ($value instanceof Database_Expression) {
					return $value->__toString();
				}
				return '"' . serialize($value) . '"';
		}
		
		throw new Database_Exception('Invalid vartype ' . gettype($value));
	}
	
	public function escape ($value) {
		if ($this->_mysqli === null) {
			// Datenbankverbindung herstellen
			$this->_connect();
		}
		
		return $this->_mysqli->real_escape_string($value);
	}
	
}