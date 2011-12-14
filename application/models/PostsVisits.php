<?php

class PostsVisits extends Model {
	
	protected $_table = 'board_postvisits';
	
	/**
	 * @param unknown_type $id
	 */
	public function find($id) {
		
	}
	
	public function count($pid) {
		return $this->_db->select('SELECT COUNT(*) AS count FROM board_postvisits WHERE pid = ' . $pid)->fetch_object()->count;
	}

	
}