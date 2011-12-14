<?php

/**
 * UserModule
 * 
 * @author benn0r <benjamin@benn0r.ch>
 * @since 29102011
 * @version 29102011
 */
class UserModule extends Module
{
	
	public function run(array $args) {
		$view = $this->view();
		$users = new Users();
		$posts = new Posts();
		$comments = new UserComments();
		$uid = $args[1];
		
		$view->u = $users->find($uid); // Angezeigter User
		$view->user = $this->getUser(); // Eingeloggter User
		
		if (isset($_POST['text'])) {
			$view->newcid = $comments->add($uid, $view->user['uid'], $_POST);
			$view->comments = $comments->fetchAll($uid, $view->user['uid'], $view->user['grade'] >= 8 ? true : false);
			
			$this->render('user', 'form');
			return;
		}
		
		if (isset($_GET['delcomment'])) {
			$comments->hide($_GET['delcomment']);
		}
		
		if (isset($_GET['restorecomment'])) {
			$comments->show($_GET['restorecomment']);
		}
		
		if (is_array($user = $this->getUser()) && $user['grade'] > 0) {
			// Aktivit�t des eingeloggten Benutzer aktualisieren
			Users::setactive($user, array('profile', $view->u->username, $uid));
		}
		
		$threads = array();
		$medias = $users->lastmedia($uid);
		$thumb = Module::init('Thumb', $this);
		
		$view->cthreads = $posts->countThreads($uid);
		$view->ccomments = $posts->countComments($uid);
		
		// Kommentare laden
		$view->comments = $comments->fetchAll($uid, $view->user['uid'], $view->user['grade'] >= 8 ? true : false);
		
		while (($post = $medias->fetch_object()) != null) {
			$width = 63 * 2;
			$height = 95;
			
			$media = new Media();
			$media->mid = $post->mid;
			$media->image = $this->_config->paths->uploads . '/' . date('Ymd', strtotime($post->inserttime)) . '/' . $post->mid . '.' . $post->image;
			
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
		}
		$view->threads = $threads;
		
		$activity = array();
		// max: 79
		for ($i = 1; $i < 79 / 2; $i++) {
			$activity[] = $users->activity($uid, time() - (86400 * $i));
		}
						
		$highest = 0;
		foreach ($activity as $i) {
			if ((int)$highest < (int)$i->total) {
				$highest = $i->total;
			}
		}
				
		$percent = array();
		foreach ($activity as $i) {
			$a = array();
			$a['percent'] = 100 * $i->total / $highest;
			//$a['percent'] = rand(0, 100);
			$a['points'] = $i->total;
			$a['threads'] = $i->threads;
			$a['comments'] = $i->comments;
			$a['guestbook'] = $i->guestbook;
			$a['datetime'] = $i->datetime;
			
			$percent[] = $a;
		}
		$view->activity = $percent;
		
		if ($_GET['ajax']) {
			$this->render('user', 'show');
		} else {
			$this->layout('user', 'show');
		}
	}
	
}