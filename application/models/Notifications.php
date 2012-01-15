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

class Notifications extends Model {
	
	protected $_table = 'board_usernotifications';
	
	public function find($pid, $admin = false) {
		
	}
	
	public function fetchAll($uid, $limit = 30) {
		$where = 'uid = ' . $uid . ' AND status > 0';
	
		$result = $this->_db->select('
			SELECT * FROM ' . $this->_table . '
			WHERE ' . $where . '
			ORDER BY inserttime DESC
			LIMIT 0, ' . $limit . '
		');
		
		return $result;
	}
	
	public function fetchAfter($uid, $nid, $limit = 10) {
		$where = 'uid = ' . $uid . ' AND status > 0 AND nid > ' . $nid;
		
		$result = $this->_db->select('
			SELECT * FROM ' . $this->_table . '
			WHERE ' . $where . '
			ORDER BY nid ASC
			LIMIT 0, ' . $limit . '
		');
		
		// Alle abgeholten Beiträge
		//$this->update(array('readtime' => new Database_Expression('NOW()')), $where);
		
		return $result;
	}	
}