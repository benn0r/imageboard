<?php

/**
 * IndexModule
 * 
 * @author benn0r <benjamin@benn0r.ch>
 * @since 29102011
 * @version 29102011
 */
class ThreadModule extends Module
{
	
	public function run(array $args) {
		$pid = (int)$args[1];
		$posts = new Posts();
		$visits = new PostsVisits();
		$tags = new PostsTags();
		$user = $this->getUser();
		
		if ($pid == 0) {
			return $this->notFound();
		}
		
		$thread = $posts->find($pid, $user['grade'] >= 8 ? true : false);
		/*if (!$thread || !$posts->isThread($thread)) {
			return $this->notFound();
		}*/
		
		if (is_array($user = $this->getUser()) && $user['grade'] > 0) {
			// Aktivität des eingeloggten Benutzer aktualisieren
			Users::setactive($user, array('thread', $pid));
		}
		
		// Laden des Thumb Module
		$thumb = Module::init('Thumb', $this);
		
		// @todo Dieser Part ist zu grossen Teilen redundant mit dem Code unten
		if (isset($_GET['load'])) {
			$view = $this->view();
			
			// Kommentare zählen, brauchen wir für den Link unten an den Kommentaren
			// Wenn es keine Kommentare mehr gibt wollen wir den Link nicht sehen
			$comments = $posts->findChilds($pid, $user['grade'] >= 8 ? true : false, 5 * $_GET['load'], 5);
			$total = $posts->countChilds($pid, $user['grade'] >= 8 ? true : false);
			$view->total = $total;
			$view->active = 5 * ($_GET['load']) + 5;
			
			$arr = array();
			while(($c = $comments->fetch_object()) != null) {
				$key = array_push($arr, $c);
				if ($c->mid) {
					$media = new Media();
								
					$media->mid = $c->mid;
					$media->name = $c->name;
					$media->description = $c->description;
					$media->published = strtotime($c->published);
					$media->author = new Media_Uri($c->author_name, $c->author_uri);
					$media->source = new Media_Uri($c->source_name, $c->source_uri);
					$media->image = 'uploads/' . date('Ymd', strtotime($c->inserttime)) . '/' . $c->mid . '.' . $c->image;
					$media->type = $c->type;
					$media->extid = $c->extid;
					
					$media->thumbnail = $thumb->getThumbnail($media,	63 * 2, 95);
					
					$arr[$key - 1]->media = $media;
				}
			}
			$view->comments = $arr;
			
			$this->render('thread', 'comments');
			exit;
		}
		
		$view = $this->view();
		$view->thread = $thread;
		$view->visits = $visits->count($pid);
		$view->tags = $tags->fetchAll($pid);
		
		// Kommentare zählen, brauchen wir für den Link unten an den Kommentaren
		// Wenn es keine Kommentare mehr gibt wollen wir den Link nicht sehen
		$total = $posts->countChilds($pid, $user['grade'] >= 8 ? true : false);
		$view->total = $total;
		$view->active = 5 * ($_GET['load']) + 5;
		
		$comments = $posts->findChilds($pid, $user['grade'] >= 8 ? true : false);
		$arr = array();
		while(($c = $comments->fetch_object()) != null) {
			$key = array_push($arr, $c);
			if ($c->mid) {
				$media = new Media();
							
				$media->mid = $c->mid;
				$media->name = $c->name;
				$media->description = $c->description;
				$media->published = strtotime($c->published);
				$media->author = new Media_Uri($c->author_name, $c->author_uri);
				$media->source = new Media_Uri($c->source_name, $c->source_uri);
				$media->image = 'uploads/' . date('Ymd', strtotime($c->inserttime)) . '/' . $c->mid . '.' . $c->image;
				$media->type = $c->type;
				$media->extid = $c->extid;
				
				$media->thumbnail = $thumb->getThumbnail($media,	63 * 2, 95);
				
				$arr[$key - 1]->media = $media;
			}
		}
		$view->comments = $arr;
		
		if (!isset($_SESSION['user_visits']) || !is_array($_SESSION['user_visits'])) {
			$_SESSION['user_visits'] = array();
		}
		
		if (isset($_SESSION['user_visits']) && !in_array($pid, $_SESSION['user_visits'])) {
			$_SESSION['user_visits'][] = $pid;
			
			$data = array(
				'pid' => $pid,
				'remote_addr' => $_SERVER['REMOTE_ADDR'],
				'http_user_agent' => $_SERVER['HTTP_USER_AGENT'],
			);
			
			if (isset($_SESSION['user'])) {
				$data['uid'] = $_SESSION['user']['uid'];
			}
						
			$visits->insert($data);
		}
		
		if ($_GET['ajax'] == 1) {
			$this->render('thread', 'thread');
		} else {
			$this->layout('thread', 'thread');
		}
	}
	
	public function notFound() {
	if ($_GET['ajax'] == 1) {
			$this->render('thread', 'notfound');
		} else {
			$this->layout('thread', 'notfound');
		}
	}
	
}