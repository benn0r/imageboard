<?php

class Boards extends Model {
	
	protected $_table = 'board_boards';
	
	public function find($pid) {
		
	}
	
	public function fetchAll() {
		$boards = array();
		
		$result = $this->_db->exec('SELECT * FROM board_boards WHERE active = 1 ORDER BY pbid,pos ASC');
		while (($row = $result->fetch_object()) !== null) {
			if ($row->pbid == 0) {
				$boards[$row->bid] = $row;
				$boards[$row->bid]->data = array();
			} else {
				if (isset($boards[$row->pbid])) {
					$boards[$row->pbid]->data[] = $row;
				}
			}
		}
		
		return $boards;
	}
	
}