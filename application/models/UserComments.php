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

class UserComments extends Model {
	
	protected $_table = 'board_usercomments';
	
	
	public function find($uid) {
		
	}
	
	public function add($ownerid, $uid, array $data) {
		$this->insert(array(
			'ownerid' => $ownerid,
			'uid' => $uid,
			'private' => isset($data['private']) ? 1 : 0,
			'text' => utf8_decode($data['text']),
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