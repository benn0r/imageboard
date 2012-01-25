<?php

/**
 * Copyright (c) 2012 benn0r <benjamin@benn0r.ch>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

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
	
	public function findByCookie($cookie, $salt) {
		return $this->_db->select('
			SELECT *
			FROM board_users
			WHERE MD5(CONCAT(uid, "' . $salt . '", username, `password`)) = "' . $this->_db->escape($cookie) . '"
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
			LEFT JOIN board_users AS c ON a.uid = c.uid
			WHERE b.mid IS NOT NULL AND a.uid = ' . (int)$uid . ' AND a.status = 1
			ORDER BY a.pid DESC
			LIMIT 0,' . $limit . ' 
		');
	}
	
	public function activity($uid, $date) {
		$yours = $this->_db->select('
			SELECT COUNT(*) AS threads
			FROM board_posts
			WHERE uid = ' . (int)$uid . ' AND status = 1 AND DATE(updatetime) = "' . date('Y-m-d', $date) . '" 
		')->fetch_object()->threads;
		
		$others = $this->_db->select('
			SELECT COUNT(*) AS threads
			FROM board_posts
			WHERE uid != ' . (int)$uid . ' AND status = 1 AND DATE(updatetime) = "' . date('Y-m-d', $date) . '"
		')->fetch_object()->threads;
		
		$obj = new stdClass();
		$obj->your = $yours;
		$obj->others = $others;
		$obj->total = $yours + $others;
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
			case 'settings':
				$obj->msg = sprintf($t->t('useraction/settings'));
				$obj->link = '';
				break;
		}
		
		return $obj;
	}
	
	public function likes($uid) {
		return $this->_db->select('
			SELECT b.*,c.*,a.updatetime AS time
			FROM board_mediaratings AS a
			LEFT JOIN board_media AS b ON a.mid = b.mid
			LEFT JOIN board_posts AS c ON b.pid = c.pid
			WHERE rating = 1 AND a.uid = ' . (int)$uid . ' AND c.pid IS NOT NULL
			ORDER BY a.updatetime DESC
			LIMIT 0,20
		');
	}
	
}