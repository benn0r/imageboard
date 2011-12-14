<?php

class Notifications extends Model {
	
	protected $_table = 'board_usernotifications';
	
	public function find($pid, $admin = false) {
		
	}
	
	public function fetchAfter($uid, $time, $limit = 10) {
		$where = 'uid = ' . $uid . ' AND status > 0 AND TIMESTAMP(inserttime) > TIMESTAMP("' . date('Y-m-d H:i:s', $time) . '")';
		
		$result = $this->_db->select('
			SELECT * FROM ' . $this->_table . '
			WHERE ' . $where . '
			ORDER BY inserttime DESC
			LIMIT 0, ' . $limit . '
		');
		
		// Alle abgeholten Beiträge
		//$this->update(array('readtime' => new Database_Expression('NOW()')), $where);
		
		return $result;
	}	
}