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
 * IndexModule
 * 
 * Generates the imageboard.
 * 
 * @author benn0r <benjamin@benn0r.ch>
 * @since 2011/10/29
 * @version 2011/12/18
 */
class IndexModule extends Module
{
	
	public function run(array $args) {
		$starttime = microtime(true);
		
		// init needed modules
		$postsTable = new Posts();
		$user = $this->getUser();
		$affiliate = new Affiliate_Privatamateure();
		$view = $this->view();
		
		$r = $this->getRequest();
		
		if ($args[0] == 'live') {
			$view->live = true;
		}
		
		if (intval($args[0]) > 0) {
			$page = intval($args[0]);
		} else {
			// lets go to page 1
			$page = 1;
		}
		$perpage = 100;
		
		if (isset($_GET['filter'])) {
			$_SESSION['filter'] = (int)$r->filter;
		}
		
		if ($view->live) {
			$posts = $postsTable->fetchLive($perpage);
		} else {
			// lets load all posts for this page (if user is admin lets load the deleted posts too)
			$posts = $postsTable->fetch(($page - 1) * $perpage, $perpage, 
					$user['grade'] >= 8 ? true : false, isset($_SESSION['filter']) ? $_SESSION['filter'] : 0);
		}
			
		// load affiliate images
		$ads = $affiliate->fetchAll();
		
		// update useractivity if user is loggedin
		if (is_array($user = $this->getUser()) && $user['grade'] > 0) {
			if ($page == 1) {
				Users::setactive($user, array('homepage'));
			} else {
				Users::setactive($user, array('board', $page));
			}
		}
		
		$count = 0; // Counter for images
		$threads = array(); // Empty array for all images
		
		$thumb = Module::init('Thumb', $this);
		
		$dwidth = 63;
		$dheight = 95;
		
		// load size array from configfile
		$sizearr = $this->getSizeConfig($dwidth, $dheight);
		
		while (($post = $posts->fetch_object()) != null) {
			list($width, $height) = $this->getSize($sizearr, $count);
			
			if ($count != 0 && $count % 7 == 0) {
				$media = new Media();
				
				$promo = $ads->fetch_object();
				
				if ($promo) {
					$media->mid = $promo->id;
					$media->promo = true;
					
					$media->username = $promo->username;
					$media->link = $promo->link;
					$media->image = $promo->image;
					
					$media->width = $width;
					$media->height = $height;
					
					$media->thumbnail = $thumb->getThumbnail($media, $width - 4, $height - 4);
					$media->lthumbnail = $thumb->getThumbnail($media, 142, 206);
				
					$threads[] = $media;
					$count++;
					
					list($width, $height) = $this->getSize($sizearr, $count);
				}
			}
			
			
			$media = new Media();
			$media->mid = $post->mid;
			
			// @todo make this somewhere else global useable
			$media->image = $this->_config->paths->uploads . '/' .
					date('Ymd', strtotime($post->inserttime)) . '/' . $post->mid . '.' . $post->image;
			
			$media->width = $width;
			$media->height = $height;
			
			// copy relevant informations from database into the media object
			$media->uid = $post->uid;
			$media->avatar = $post->avatar;
			$media->username = $post->username;
			$media->updatetime = $post->updatetime;
			$media->pid = $post->pid;
			$media->ppid = $post->ppid;
			$media->status = $post->astatus;
			$media->birthday = $post->birthday;
			$media->pinned = $post->pinned;
			
			$media->type = $post->type;
			
			// load thumbnail
			$media->thumbnail = $thumb->getThumbnail($media, $width - 4, $height - 4);
			
			// load second thumbnail which appears when the mouse hovers the first thumbnail
			$media->lthumbnail = $thumb->getThumbnail($media, 142, 206);
			
			$threads[] = $media;
			
			$count++;
		}
		
		$pager = new cwpagination();
		$pager->newpagination();
		$pager->setlink($view->baseUrl() . "$$$");
		$pager->setamount($perpage);
		$pager->setcontent($postsTable->countAll($user['grade'] >= 8 ? true : false, 
				isset($_SESSION['filter']) ? $_SESSION['filter'] : 0));
		$pager->setjump(false);
		$pager->setroot(true);
		$pager->setpage($page);
		$pager->setstyle("class","pagination3");
		$view->navigation = $pager;
		
		$view->posts = $threads;
		$view->page = $page;
		
		$endtime = microtime(true);
		$view->time = $endtime - $starttime;
		
		$tags = new Tags();
		$view->tags = $tags->fetchCategories();
		
		if (isset($_GET['board'])) {
			$this->render('board', 'imageboard');
		} elseif (isset($_GET['ajax'])) {
			$this->render('board', 'board');
		} else {
			$this->layout('board', 'board');
		}
	}
	
	public function getSize($sizearr, $count) {
		// default width and height
		$width = 63;
		$height = 95;
		
		foreach ($sizearr as $size) {
			switch ($size['coord']) {
				case 'x':
					if (in_array($count, $size['images'])) {
						$width = $size['val'];
					}
					break;
				case 'y':
					if (in_array($count, $size['images'])) {
						$height = $size['val'];
					}
					break;
			}
		}
		
		return array($width, $height);
	}
	
	/**
	 * I really like this part!
	 *
	 * This method alters the config in section "board" into an for the
	 * script readable array.
	 *
	 * Before i made this the sizes of the images in the board were hardcoded
	 * in the script. I checked the performance before and after that change
	 * and its not a problem if i use a tmp-file to store the generated array.
	 *
	 * Sample for the config:
	 * size[x*3] = 0-3,5	->	x stands for width (y for height), this example
	 * will match to the images 0,1,2,3 and 5 and will  triple its width.
	 *
	 * Its only multiplication allowed because addition and substraction makes
	 * not very much sense, i guess.
	 *
	 * @param int $dwidth default width
	 * @param int $dheight default height
	 * @return array
	 */
	public function getSizeConfig($dwidth, $dheight) {
		if (file_exists($file = $this->getConfig()->paths->boardtmp)) {
			// Array is already saved, lets take this!
			return unserialize(file_get_contents($file));
		}
	
		$size = $this->getConfig()->board->toArray();
		$sizearr = array();
		foreach ($size['size'] as $key => $images) {
			$splitted = preg_split('/(\*|\+|\-|\/)/', $key);
			if (strpos($key, '*')) {
				// generates the new width resp. height
				$val = str_replace(array('x', 'y'), $splitted[0] == 'x' ? $dwidth : $dheight, $splitted[0]) * $splitted[1];
			}
	
			$data = array();
			$data['coord'] = $splitted[0]; // save 'x' or 'y'
			$data['images'] = $this->splitIntegers($images); // all appropriate numbers
			$data['val'] = $val; // new width (x) or height (y)
			$sizearr[] = $data;
		}
	
		// Save array for later use
		file_put_contents($file, serialize($sizearr));
	
		return $sizearr;
	}
	
	/**
	 * Converts a string with integers, into an array.
	 *
	 * Example string:
	 * 0-3,5-8,10,12-15,17 this example will return
	 * an array with the content 0, 1, 2, 3, 5, 6, 7,
	 * 8, 10, 12, 13, 14, 15 and 17
	 *
	 * @param string $str
	 * @param string $splitter Splitter for explode
	 * @return array Array with all integers
	 */
	public function splitIntegers($str, $splitter = ',', $hyphen = '-') {
		$arr = explode($splitter, $str);
		$return = array();
		foreach ($arr as $str) {
			$ints = explode($hyphen, $str);
	
			// Add new values with phpfunction "range"
			$return = array_merge($return, range($ints[0], count($ints) > 1 ? $ints[1] : $ints[0]));
		}
	
		return $return;
	}
	
}