<?php

class Users extends Model {
	
	protected $_table = 'board_users';
	
	public function findUser($username, $password) {
		return $this->_db->select('
			SELECT *
			FROM board_users
			WHERE username = "' . $this->_db->escape($username) . '" AND
				password = MD5("' . $this->_db->escape($password) . '")
		');
	}
	
	public function findUserbyEmail($email) {
		return $this->_db->select('
			SELECT *
			FROM board_users
			WHERE email = "' . $this->_db->escape($email) . '"
		');
	}
	
	public function findUserByName($name) {
		return $this->_db->select('
			SELECT *
			FROM board_users
			WHERE username = "' . $this->_db->escape($name) . '"
		');
	}
	
	public function updatePassword($uid, $password) {
		$this->update(array('password' => md5($password)), 'uid = ' . (int)$uid);
	}
	
	public function find($uid) {
		return $this->_db->select('
			SELECT *
			FROM board_users AS a
			LEFT JOIN board_users AS c ON a.uid = c.uid
			LEFT JOIN board_userstyles AS d ON c.sid = d.sid
			WHERE a.uid = ' . (int)$uid . '
		')->fetch_object();
	}
	
	public function lastmedia($uid, $limit = 9) {
		return $this->_db->select('
			SELECT *
			FROM board_posts AS a
			LEFT JOIN board_media AS b ON a.pid = b.pid
			WHERE b.mid IS NOT NULL AND uid = ' . (int)$uid . ' AND a.status = 1
			ORDER BY a.pid DESC
			LIMIT 0,' . $limit . ' 
		');
	}
	
	public function activity($uid, $date) {
		$threads = $this->_db->select('
			SELECT COUNT(*) AS threads
			FROM board_posts
			WHERE ppid IS NULL AND uid = ' . (int)$uid . ' AND status = 1 AND DATE(updatetime) = "' . date('Y-m-d', $date) . '" 
		')->fetch_object()->threads;
		
		$comments = $this->_db->select('
			SELECT COUNT(*) AS threads
			FROM board_posts
			WHERE ppid > 0 AND uid = ' . (int)$uid . ' AND status = 1 AND DATE(updatetime) = "' . date('Y-m-d', $date) . '" 
		')->fetch_object()->threads;
		
		$guestbook = $this->_db->select('
			SELECT COUNT(*) AS threads
			FROM board_usercomments
			WHERE uid = ' . (int)$uid . ' AND status = 1 AND DATE(inserttime) = "' . date('Y-m-d', $date) . '" 
		')->fetch_object()->threads;
		
		$obj = new stdClass();
		$obj->threads = $threads;
		$obj->comments = $comments;
		$obj->guestbook = $guestbook;
		$obj->total = ($threads * 2) + $comments + $guestbook;
		$obj->datetime = $date;
		
		return $obj;
	}
	
	public function active($limit = 10) {
		// $dbadapter->exec('UPDATE board_users SET last_activity = NOW(),online=1,remote_addr="'.$_SERVER['REMOTE_ADDR'].'" WHERE uid="'.(int)$_SESSION['user']->uid.'"');
		
		// Alle Nutzer die mehr als 15 Minuten inaktiv waren werden auf offline gesetzt
		$this->update(array('online' => 0), 'last_activity < (NOW() - INTERVAL 15 MINUTE)');
		
		return $this->_db->select('
			SELECT *
			FROM board_users AS a
			WHERE online = 1
			ORDER BY last_activity DESC
			LIMIT 0,' . $limit . ' 
		');
	}
	
	static public function setactive(array $user, array $data) {
		if (isset($user['uid'])) {
			$model = new Users();
			
			$model->update(array(
				'last_activity' => new Database_Expression('NOW()'),
				'activity_text' => implode(';', $data),
				'online' => 1,
			), 'uid = ' . (int)$user['uid']);
			
			return true;
		}
		
		return false;
	}
	
	static public function printactivity($data, Language $t) {
		$data = explode(';', $data);
		
		$obj = new stdClass();
		
		switch ($data[0]) {
			case 'profile':
				$obj->msg = sprintf($t->t('useraction/profile'), $data[1]);
				$obj->link = 'user/' . $data[2] . '/';
				break;
			case 'thread':
				$obj->msg = $t->t('useraction/thread');
				$obj->link = 'thread/' . $data[1] . '/';
				break;
			case 'homepage':
				$obj->msg = $t->t('useraction/homepage');
				$obj->link = '';
				break;
			case 'board':
				$obj->msg = sprintf($t->t('useraction/board'), $data[1]);
				$obj->link = '' . $data[1] . '/';
				break;
		}
		
		return $obj;
	}
	
}