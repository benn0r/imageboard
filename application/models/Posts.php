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

class Posts extends Model {
	
	protected $_table = 'board_posts';
	
	public function find($pid, $admin = false) {
		return $this->_db->select('
			SELECT *,a.status AS astatus,a.pid FROM board_posts AS a
			LEFT JOIN board_media AS b ON a.pid = b.pid
			LEFT JOIN board_users AS c ON a.uid = c.uid
			LEFT JOIN board_userstyles AS d ON c.sid = d.sid
			WHERE ' . ($admin == true ? '' : 'a.status = 1 AND ') . 'a.pid = ' . $pid . '
		')->fetch_object();
	}
	
	public function next($pid, $tag = 0) {
		$rowset = $this->_db->select('
			SELECT a.pid FROM board_posts AS a
			LEFT JOIN board_media AS c ON a.pid = c.pid
			' . ($tag ? 'LEFT JOIN board_posts2tags AS d ON a.pid = d.pid' : '') . '
			WHERE a.pid > ' . (int)$pid . ' AND a.ppid IS NULL AND a.status = 1 AND c.mid IS NOT NULL
			' . ($tag ? ' AND d.tid = ' . $tag : '') . '
			ORDER BY a.pid ASC
			LIMIT 0,1
		');
		
		if ($rowset->num_rows > 0) {
			return $rowset->fetch_object()->pid;
		}
		
		return null;
	}
	
	public function prev($pid, $tag = 0) {
		$rowset = $this->_db->select('
			SELECT a.pid FROM board_posts AS a
			LEFT JOIN board_media AS c ON a.pid = c.pid
			' . ($tag ? 'LEFT JOIN board_posts2tags AS d ON a.pid = d.pid' : '') . '
			WHERE a.pid < ' . (int)$pid . ' AND a.ppid IS NULL AND a.status = 1 AND c.mid IS NOT NULL
			' . ($tag ? ' AND d.tid = ' . $tag : '') . '
			ORDER BY a.pid DESC
			LIMIT 0,1
		');
	
		if ($rowset->num_rows > 0) {
			return $rowset->fetch_object()->pid;
		}
	
		return null;
	}
	
	public function fetchMedia($pid, $admin = false) {
		return $this->_db->select('
			SELECT * FROM board_media AS a
			WHERE ' . ($admin == true ? '' : 'a.status = 1 AND ') . 'a.pid = ' . $pid . '
		');
	}
	
	public function countThreads($uid) {
		return $this->_db->select('
			SELECT COUNT(*) AS cthreads FROM board_posts AS a
			WHERE a.ppid IS NULL AND a.status = 1 AND a.uid = ' . (int)$uid . '
		')->fetch_object()->cthreads;
	}
	
	public function countComments($uid) {
		return $this->_db->select('
			SELECT COUNT(*) AS cthreads FROM board_posts AS a
			WHERE a.ppid IS NOT NULL AND a.status = 1 AND a.uid = ' . (int)$uid . '
		')->fetch_object()->cthreads;
	}
	
	public function countAll($admin = false, $tag = 0) {		
		$result = $this->_db->exec('
			SELECT COUNT(*) AS posts 
			FROM board_media AS a
			LEFT JOIN board_posts AS b ON a.pid = b.pid
			' . ($tag ? 'LEFT JOIN board_posts2tags AS d ON b.pid = d.pid' : '') . '
			WHERE a.status = 1
			' . ($tag ? ' AND d.tid = ' . $tag : '') . '
		');
		
		$row = $result->fetch_object();
		
		return $row->posts;
	}
	
	public function isThread(stdClass $obj) {
		if ($obj->ppid == NULL && $obj->replyto == 0) {
			return true;
		}
		return false;
	}
	
	public function fetch($from, $to, $admin = false, $tag = 0) {
		return $this->_db->select('
				SELECT a.*,b.*,c.*,a.status AS astatus FROM board_posts AS a
				LEFT JOIN board_media AS b ON a.pid = b.pid
				LEFT JOIN board_users AS c ON a.uid = c.uid
				' . ($tag ? 'LEFT JOIN board_posts2tags AS d ON a.pid = d.pid
						LEFT JOIN board_posts2tags AS e ON a.ppid = e.pid' : '') . '
				WHERE ' . ($admin == true ? '' : 'a.status = 1 AND ') . 'b.status = 1
				' . ($tag ? ' AND (d.tid = ' . $tag . ' OR e.tid = ' . $tag . ')' : '') . '
				GROUP BY b.mid
				ORDER BY a.pinned DESC,a.pid DESC
				LIMIT ' . $from . ', ' . $to . '
				');
	}
	
	public function fetchLive($to) {
		return $this->_db->select('
			SELECT a.*,b.*,c.*,a.status AS astatus FROM board_posts AS a
			LEFT JOIN board_media AS b ON a.pid = b.pid
			LEFT JOIN board_users AS c ON a.uid = c.uid
			WHERE a.status = 1 AND b.status = 1
			GROUP BY b.mid
			ORDER BY (SELECT d.pid FROM board_posts AS d WHERE d.ppid = a.pid OR d.pid = a.pid ORDER BY d.pid DESC LIMIT 0,1) DESC
			LIMIT 0, ' . $to . '
		');
	}
	
	public function findChilds($pid, $admin = false, $from = 0, $to = 10, $find = null) {
		return $this->_db->select('
			SELECT a.*,c.*,d.*,a.status AS astatus, f.username as pusername FROM board_posts AS a
			LEFT JOIN board_users AS c ON a.uid = c.uid
			LEFT JOIN board_userstyles AS d ON c.sid = d.sid
			LEFT JOIN board_posts AS e ON a.replyto = e.pid
			LEFT JOIN board_users AS f ON e.uid = f.uid
			WHERE ' . ($admin == true ? '' : 'a.status = 1 AND ') . 'a.ppid = ' . (int)$pid . '
				' . ($find ? ' AND a.pid >= ' . $find . ' ' : '') . '
			ORDER BY a.pid DESC
			' . ($find ? '' : 'LIMIT ' . $from . ', ' . $to) . '
		');
	}
	
	public function countChilds($pid, $admin = false) {
		$count = $this->_db->select('
			SELECT COUNT(*) AS childs FROM board_posts AS a
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