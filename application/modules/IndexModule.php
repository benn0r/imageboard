<?php

/**
 * IndexModule
 * 
 * @author benn0r <benjamin@benn0r.ch>
 * @since 2011/10/29
 * @version 2011/12/18
 */
class IndexModule extends Module
{
	
	public function run(array $args) {
		$starttime = microtime(true);
		
		$postsTable = new Posts();
		$boardsTable = new Boards();
		$user = $this->getUser();
		
		if (intval($args[0]) > 0) {
			$page = intval($args[0]);
		} else {
			$page = 1;
		}
		$perpage = 100;
				
		$posts = $postsTable->fetch(($page - 1) * $perpage, $perpage, $user['grade'] >= 8 ? true : false);
		
		if (is_array($user = $this->getUser()) && $user['grade'] > 0) {
			// Aktivität des eingeloggten Benutzer aktualisieren
			if ($page == 1) {
				Users::setactive($user, array('homepage'));
			} else {
				Users::setactive($user, array('board', $page));
			}
		}
		
		$count = 0;
		$threads = array();
		
		$thumb = Module::init('Thumb', $this);
		
		$dwidth = 63;
		$dheight = 95;
		
		$sizearr = $this->getSizeConfig($dwidth, $dheight);
		
		while (($post = $posts->fetch_object()) != null) {
			$width = $dwidth;
			$height = $dheight;
			
			$media = new Media();
			$media->mid = $post->mid;
			$media->image = $this->_config->paths->uploads . '/' . date('Ymd', strtotime($post->inserttime)) . '/' . $post->mid . '.' . $post->image;
			
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
			
			/*switch(true) {
				case ($posts->num_rows < $perpage):
					$width = 2 * $width;
					break;
				case (($count == 0) || ($count == 1) || ($count == 2) || ($count == 21)):
					$width = 3 * $width;
					$height = 3 * $height;
					break;
				case (($count >= 1 && $count <= 13) || ($count >= 19 && $count <= 20) || ($count == 23)
				 || ($count >= 35 && $count <= 39) || ($count == 42) || ($count == 45) || ($count == 47)
				|| ($count == 50) || ($count == 52)|| ($count == 54) || ($count == 55) || ($count == 57)):
					$width = 2 * $width;
					break;
				case (($count >= 26 && $count <= 32) || ($count == 17) || ($count == 34)):
					$width = 2 * $width;
					$height = 2 * $height;
					break;
			}*/
			
			$media->width = $width;
			$media->height = $height;
			$media->uid = $post->uid;
			$media->avatar = $post->avatar;
			$media->username = $post->username;
			$media->updatetime = $post->updatetime;
			$media->pid = $post->pid;
			$media->ppid = $post->ppid;
			$media->status = $post->astatus;
						
			$media->thumbnail = $thumb->getThumbnail($media, $width - 4, $height - 4);
			$media->lthumbnail = $thumb->getThumbnail($media, 142, 206);
			
			$threads[] = $media;
			
			$count++;
		}
		
		$pager = new cwpagination();
		$pager->newpagination();
		$pager->setlink("$$$");
		$pager->setamount($perpage);
		$pager->setcontent($postsTable->countAll());
		$pager->setjump(true);
		$pager->setroot(true);
		$pager->setpage($page);
		$pager->setstyle("class","pagination3");
		$this->view()->navigation = $pager;
		
		$this->view()->posts = $threads;
		$this->view()->boards = $boardsTable->fetchAll();
		
		$endtime = microtime(true);
		$this->view()->time = $endtime - $starttime;
		
		if ($_GET['ajax']) {
			$this->render('board', 'board');
		} else {
			$this->layout('board', 'board');
		}
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