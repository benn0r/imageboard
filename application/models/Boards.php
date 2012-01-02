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