<?php

class Posts extends Model {
	
	protected $_table = 'board_posts';
	
	public function find($pid, $admin = false) {
		return $this->_db->select('
			SELECT *,a.status AS astatus FROM board_posts AS a
			LEFT JOIN board_media AS b ON a.pid = b.pid
			LEFT JOIN board_users AS c ON a.uid = c.uid
			LEFT JOIN board_userstyles AS d ON c.sid = d.sid
			WHERE ' . ($admin == true ? '' : 'a.status = 1 AND ') . 'a.pid = ' . $pid . '
		')->fetch_object();
	}
	
	public function countThreads($uid) {
		return $this->_db->select('
			SELECT COUNT(*) AS cthreads FROM board_posts AS a
			WHERE a.ppid IS NULL AND a.status = 1 AND a.uid = ' . $uid . '
		')->fetch_object()->cthreads;
	}
	
	public function countComments($uid) {
		return $this->_db->select('
			SELECT COUNT(*) AS cthreads FROM board_posts AS a
			WHERE a.ppid IS NOT NULL AND a.status = 1 AND a.uid = ' . $uid . '
		')->fetch_object()->cthreads;
	}
	
	public function countAll() {		
		$result = $this->_db->exec('SELECT COUNT(*) AS posts FROM board_media WHERE status = 1');
		$row = $result->fetch_object();
		
		return $row->posts;
	}
	
	public function isThread(stdClass $obj) {
		if ($obj->ppid == NULL && $obj->replyto == 0) {
			return true;
		}
		return false;
	}
	
	public function fetch($from, $to, $admin = false) {
		return $this->_db->select('
			SELECT *,a.status AS astatus FROM board_posts AS a
			LEFT JOIN board_media AS b ON a.pid = b.pid
			LEFT JOIN board_users AS c ON a.uid = c.uid
			WHERE ' . ($admin == true ? '' : 'a.status = 1 AND ') . 'b.status = 1
			ORDER BY a.pid DESC
			LIMIT ' . $from . ', ' . $to . '
		');
	}
	
	public function findChilds($pid, $admin = false, $from = 0, $to = 5) {		
		return $this->_db->select('
			SELECT a.*,b.*,c.*,d.*,a.status AS astatus, f.username as pusername FROM board_posts AS a
			LEFT JOIN board_media AS b ON a.pid = b.pid
			LEFT JOIN board_users AS c ON a.uid = c.uid
			LEFT JOIN board_userstyles AS d ON c.sid = d.sid
			LEFT JOIN board_posts AS e ON a.replyto = e.pid
			LEFT JOIN board_users AS f ON e.uid = f.uid
			WHERE ' . ($admin == true ? '' : 'a.status = 1 AND ') . 'a.ppid = ' . (int)$pid . '
			ORDER BY a.pid DESC
			LIMIT ' . $from . ', ' . $to . '
		');
	}
	
	public function countChilds($pid, $admin = false) {
		$count = $this->_db->select('
			SELECT COUNT(*) AS childs FROM board_posts AS a
			LEFT JOIN board_media AS b ON a.pid = b.pid
			WHERE ' . ($admin == true ? '' : 'a.status = 1 AND ') . 'a.ppid = ' . (int)$pid . '
			ORDER BY a.pid DESC
		');
		
		return $count->fetch_object()->childs;
	}
	
	public function hide($pid) {
		$this->update(array('status' => 0), 'pid = ' . $pid);
	}
	
	public function show($pid) {
		$this->update(array('status' => 1), 'pid = ' . $pid);
	}
	
}