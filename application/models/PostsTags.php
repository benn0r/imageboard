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