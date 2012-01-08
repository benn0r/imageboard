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

class MediaRatings extends Model {
	
	protected $_table = 'board_mediaratings';
	
	public function find($mid, $uid = null) {
		return $this->getDb()->select('SELECT * FROM board_mediaratings WHERE mid = ' . $mid
				. ($uid ? ' AND uid = ' . $uid : ''));
	}
	
	/**
	 * delets a rating from this user for this media
	 * 
	 * @param int $mid mediaid
	 * @param int $uid userid
	 */
	public function delRatings($mid, $uid) {
		$this->getDb()->exec('DELETE FROM board_mediaratings WHERE mid = ' . (int)$mid . ' AND uid = ' . (int)$uid);
	}
	
	/**
	 * 
	 * 
	 * @param int $mid mediaid
	 * @param int $uid userid
	 * @param int $rating 0 or 1 (thump up or down)
	 */
	public function addRating($mid, $uid, $rating) {
		$this->insert(array(
				'mid' => $mid,
				'uid' => $uid,
				'rating' => $rating,
		));
	}
	
}