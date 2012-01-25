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
 * @author benn0r <benjamin@benn0r.ch>
 * @since 2011/10/29
 * @version 2012/01/08
 */
class ThreadModule extends Module
{
	
	public function run(array $args) {
		$pid = (int)$args[1];
		$posts = new Posts();
		$visits = new PostsVisits();
		$tags = new PostsTags();
		$user = $this->getUser();
		$view = $this->view();
		
		if ($pid == 0) {
			return $this->notFound();
		}
		
		$thread = $posts->find($pid, $user['grade'] >= 8 ? true : false);
		
		if (!$thread) {
			return $this->notFound();
		}
		
		$rowset = $posts->fetchMedia($pid, $user['grade'] >= 8 ? true : false);
		
		// load module for generating thumbnails
		$thumb = Module::init('Thumb', $this);
		
		$allmedia = array();
		while (($c = $rowset->fetch_object()) != null) {
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
			$media->default = $c->default;
			$media->filename = $c->filename;
			
			$media->thumbnail = $thumb->getThumbnail($media, 90, 90);
			
			$allmedia[] = $media;
		}
		
		if (isset($args[2]) && $args[2] > 0) {
			$view->mid = $mid = $args[2];
			foreach ($allmedia as $media) {
				if ($media->mid == $mid) {
					$view->media = $media;
				}
			}
		} else {
			foreach ($allmedia as $media) {
				if ($media->default == 1) {
					$view->mid = $media->mid;
					$view->media = $media;
				}
			}
			
			if (!$view->media) {
				// take first media
				$view->mid = $allmedia[0]->mid;
				$view->media = $allmedia[0];
			}
		}
				
		if ($view->media && isset($_SESSION['user'])) {
			// load rating
			$ratings = new MediaRatings();
			$rowset = $ratings->find($view->media->mid, $user['uid']);
			
			if ($rowset->num_rows > 0) {
				$view->rating = $rating = $rowset->fetch_object();
			}
			
			// load ratings for this media
			$rating = Module::init('Rating', $this);
			$view->ratingbar = $rating->run(array('rating', 'show', $view->media->mid, 'parent'));
		}
		
		$view->pid = $pid;
		$view->mediaset = $allmedia;
		
		/*if (!$thread || !$posts->isThread($thread)) {
			return $this->notFound();
		}*/
		
		if (is_array($user = $this->getUser()) && $user['grade'] > 0) {
			// Aktivität des eingeloggten Benutzer aktualisieren
			Users::setactive($user, array('thread', $pid));
		}
		
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
				$rowset = $posts->fetchMedia($c->pid);
				$mediaset = array();
				while(($row = $rowset->fetch_object()) != null) {
					$media = new Media();
												
					$media->mid = $row->mid;
					$media->name = $row->name;
					$media->description = $row->description;
					$media->published = strtotime($row->published);
					$media->author = new Media_Uri($row->author_name, $row->author_uri);
					$media->source = new Media_Uri($row->source_name, $row->source_uri);
					$media->image = $this->getConfig()->paths->uploads . '/' . date('Ymd', strtotime($row->inserttime)) . '/' . $row->mid . '.' . $row->image;
					$media->type = $row->type;
					$media->extid = $row->extid;
					$media->pid = $row->pid;
					
					$media->thumbnail = $thumb->getThumbnail($media, 90, 90);
					
					$mediaset[] = $media;
				}
				$c->mediaset = $mediaset;
				
				$arr[] = $c;
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
		$view->active = 5 * (isset($_GET['load'])) + 5;
		
		$comments = $posts->findChilds($pid, $user['grade'] >= 8 ? true : false, 0, 10,
				(isset($_GET['goto']) ? $_GET['goto'] : null));
		$arr = array();
		
		while(($c = $comments->fetch_object()) != null) {
			$rowset = $posts->fetchMedia($c->pid);
			$mediaset = array();
			while(($row = $rowset->fetch_object()) != null) {
				$media = new Media();
											
				$media->mid = $row->mid;
				$media->name = $row->name;
				$media->description = $row->description;
				$media->published = strtotime($row->published);
				$media->author = new Media_Uri($row->author_name, $row->author_uri);
				$media->source = new Media_Uri($row->source_name, $row->source_uri);
				$media->image = $this->getConfig()->paths->uploads . '/' . date('Ymd', strtotime($row->inserttime)) . '/' . $row->mid . '.' . $row->image;
				$media->type = $row->type;
				$media->extid = $row->extid;
				$media->pid = $row->pid;
				$media->default = $row->default;
				
				$media->thumbnail = $thumb->getThumbnail($media, 90, 90);
				
				$mediaset[] = $media;
			}
			$c->mediaset = $mediaset;
			
			$arr[] = $c;
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
		
		$view->next = $posts->next($pid, isset($_SESSION['filter']) ? $_SESSION['filter'] : 0);
		$view->prev = $posts->prev($pid, isset($_SESSION['filter']) ? $_SESSION['filter'] : 0);
		
		/**
		 * library for captcha
		 */
		require_once('recaptchalib.php');
		
		if (isset($_GET['ajax']) == 1) {
			$this->render('thread', 'thread');
		} else {
			$this->layout('thread', 'thread');
		}
	}
	
	public function notFound() {
		if (isset($_GET['ajax'])) {
			$this->render('thread', 'notfound');
		} else {
			$this->layout('thread', 'notfound');
		}
	}
	
}