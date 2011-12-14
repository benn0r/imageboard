<?php

class PostsTags extends Model {
	
	protected $_table = 'board_posts2tags';
	
	public function find($pid) {
		
	}
	
	public function fetchAll($pid) {
		return $this->_db->select('
			SELECT * FROM board_tags AS a
			LEFT JOIN board_posts2tags AS b ON a.tid = b.tid
			WHERE b.pid = ' . $pid . '
			ORDER BY a.tid DESC
		');
	}
	
}