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

/**
 * RatingModule
 * 
 * @author benn0r <benjamin@benn0r.ch>
 * @since 2012/01/08
 * @version 2012/01/08
 */
class RatingModule extends Module
{
	
	/**
	 * Called from clients which want to like or dislike a media
	 * (only called regulary with ajax)
	 * 
	 * @param array $args not interesting in this module
	 */
	public function run(array $args) {
		$r = $this->getRequest();
		$ratings = new MediaRatings();
		$user = $this->getUser();
		
		$mid = $args[2];
		
		if (isset($_SESSION['user'])) switch ($args[1]) {
			case 'like':
				$ratings->delRatings($mid, $user['uid']); // clean table first
				$ratings->addRating($mid, $user['uid'], 1);
				break;
			case 'dislike':
				$ratings->delRatings($mid, $user['uid']); // clean table first
				$ratings->addRating($mid, $user['uid'], 0);
				break;
		}
		
		if (isset($_SESSION['user']) && $mid) {
			$rowset = $ratings->find($mid);
			
			$like = 0;
			$dislike = 0;
			
			while (($row = $rowset->fetch_object()) != null) {
				if ($row->rating) $like++;
				else $dislike++;
			}
			
			$return = new stdClass();
			
			// lets always round likes up :)
			$return->like = ceil($like / (($like + $dislike) / 100));
			
			// lets always round dislikes up
			$return->dislike = floor($dislike / (($like + $dislike) / 100));
			
			echo json_encode($return);
		}
	}
	
}