<?php

/**
 * IndexModule
 * 
 * @author benn0r <benjamin@benn0r.ch>
 * @since 29102011
 * @version 29102011
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
		
		while (($post = $posts->fetch_object()) != null) {
			$width = 63;
			$height = 95;
			
			$media = new Media();
			$media->mid = $post->mid;
			$media->image = $this->_config->paths->uploads . '/' . date('Ymd', strtotime($post->inserttime)) . '/' . $post->mid . '.' . $post->image;
			
			switch(true) {
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
			}
			
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
	
}