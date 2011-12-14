<?php

class News extends Model {
	
	protected $_table = 'board_news';
	
	public function find($pid, $admin = false) {
		
	}
	
	public function fetch($limit = 5) {
		$where = 'status > 0';
		
		$result = $this->_db->select('
			SELECT * FROM ' . $this->_table . '
			WHERE ' . $where . '
			ORDER BY nid DESC
		');

		return $result;
	}	
	
	public function fetchAfter($time) {
		$where = 'status > 0 AND TIMESTAMP(inserttime) > TIMESTAMP("' . date('Y-m-d H:i:s', $time) . '")';
		
		$result = $this->_db->select('
			SELECT * FROM ' . $this->_table . '
			WHERE ' . $where . '
			ORDER BY nid DESC
		');
		
		// Alle abgeholten Beiträge
		//$this->update(array('readtime' => new Database_Expression('NOW()')), $where);
		
		return $result;
	}	
}