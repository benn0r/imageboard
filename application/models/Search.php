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

class Search {
	
	const TABLE_USERS = 1;
	const TABLE_POSTS = 2;
	
	public $enabled = array(
		'users', 'threads', 'comments'
	);
	
	private $_terms = array();
	
	private $_db;
	
	public function __construct(Database $db) {
		$this->_db = $db;
	}
	
	public function addTerm($term) {
		$this->_terms[] = $term;
	}
	
	public function index() {
		$db = $this->_db;
		$index = new Searchindex();
		
		// remove index
		$db->exec('TRUNCATE TABLE board_search');
		
		// add users
		$users = $db->select('SELECT uid,username,avatar FROM board_users');
		while (($row = $users->fetch_object()) != null) {
			$index->addData(self::TABLE_USERS, $row->uid, $row->username, 0, 'avatars/' . $row->uid . '.' . $row->avatar);
		}
		
		// add posts
		$posts = $db->select('SELECT pid,ppid,content FROM board_posts WHERE status = 1 AND content != ""');
		while (($row = $posts->fetch_object()) != null) {
			$index->addData(self::TABLE_POSTS, $row->pid, $row->content, $row->ppid);
		}
		
	}
	
	public function search() {
		$db = $this->_db;
		
		$terms = '';
		foreach ($this->_terms as $term) {
			$terms .= '' . $term . ' ';
		}
		
		$results = $db->select('SELECT *,MATCH (content) AGAINST ("' . $terms . '" WITH QUERY EXPANSION) AS score FROM board_search
				WHERE MATCH (content) AGAINST ("' . $terms . '" WITH QUERY EXPANSION)
				GROUP BY `table`,pk');
		
		$json = array();
		while (($row = $results->fetch_object()) != null) {
			$json[] = $row;
		}
		
		echo json_encode($json);
		
		/*$results = array();
		foreach ($this->enabled as $func) {
			$results[] = $this->$func();
		}*/
	}
	
	public function users() {
		$db = $this->_db;
		
		$terms = '';
		foreach ($this->_terms as $term) {
			$terms .= $term . ' ';
		}
		
		$search = $db->select('SELECT id, MATCH (content) AGAINST ("' . $terms . '") FROM board_search');
	}
	
}