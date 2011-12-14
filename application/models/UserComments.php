<?php

class UserComments extends Model {
	
	protected $_table = 'board_usercomments';
	
	
	public function find($uid) {
		
	}
	
	public function add($ownerid, $uid, array $data) {
		$this->insert(array(
			'ownerid' => $ownerid,
			'uid' => $uid,
			'private' => isset($data['private']) ? 1 : 0,
			'text' => $data['text'],
		));
		
		return $this->_db->lastInsertId();
	}
	
	public function hide($cid) {
		$this->update(array('status' => 0), 'cid = ' . (int)$cid);
	}
	
	public function show($cid) {
		$this->update(array('status' => 1), 'cid = ' . (int)$cid);
	}
	
	public function fetchAll($ownerid, $uid, $admin = false) {
		return $this->_db->select('
			SELECT *, a.status AS astatus
			FROM board_usercomments AS a
			LEFT JOIN board_users AS b ON a.uid = b.uid
			WHERE ' . ($admin == true ? '' : 'a.status = 1 AND ') . ' 
				ownerid = ' . (int)$ownerid . ' ' . ($ownerid == $uid ? '' : 'AND (a.private = 0 OR a.uid = ' . $uid . ')') . '
			ORDER BY a.cid DESC
		');
	}
	
}