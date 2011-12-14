<?php

/**
 * Database_Expression
 * 
 * @author benn0r <benjamin@benn0r.ch>
 * @since 29102011
 * @version 29102011
 */
class Database_Expression
{
	
	/**
	 * Expression
	 * 
	 * @var string
	 */
	private $_data = '';
	
	/**
	 * Speichert den Ausdruck in $_data
	 * 
	 * @param string $data
	 */
	public function __construct($data) {
		$this->_data = $data;
	}
	
	/**
	 * Liefert den Ausdruck zurueck 
	 * 
	 * @return string Expression
	 */
	public function __toString() {
		return $this->_data;
	}
	
}