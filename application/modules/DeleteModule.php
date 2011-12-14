<?php

/**
 * IndexModule
 * 
 * @author benn0r <benjamin@benn0r.ch>
 * @since 29102011
 * @version 29102011
 */
class DeleteModule extends Module
{
	
	public function run(array $args) {
		$pid = (int)$_GET['pid'];
		$posts = new Posts();
		
		if ($pid == 0) {
			return $this->notFound();
		}
		
		// Eingeloggter Benutzer
		$u = $this->getUser();
		
		// Zu l�schender Thread
		$p = $posts->find($pid);
		
		// Rechte �berpr�fen
		if ($p && is_array($u) && ($u['uid'] == $p->user_id || $u['grade'] >= 8)) {
			// Benutzer ist Besitzer oder mindestens Moderator
			
			if (isset($_GET['delete'])) {
				// Thread l�schen
				$posts->hide($pid);
				
				// Alle Kommentare l�schen
				$childs = $posts->findChilds($pid);
				while (($c = $childs->fetch_object()) != null) {
					$posts->hide($c->pid);
				}
				
				if ($u['grade'] >= 8) {
					header('Location: ' . $this->view()->baseUrl() . 'thread/' . $_GET['pid'] . '/?ajax=1');
				} else {
					header('Location: ' . $this->view()->baseUrl() . '?ajax=1');
				}
				exit;
			} elseif (isset($_GET['cancel'])) {
				header('Location: ' . $this->view()->baseUrl() . 'thread/' . $_GET['pid'] . '/?ajax=1');
				exit;
			} elseif (!isset($_GET['restore'])) {
				$thumb = Module::init('Thumb', $this);
				$view = $this->view();
				
				$c = $p; // Zu faul um alle Variablen auf $p zu �ndern
				$media = new Media();
						
				$media->mid = $c->mid;
				$media->name = $c->name;
				$media->description = $c->description;
				$media->published = strtotime($c->published);
				$media->author = new Media_Uri($c->author_name, $c->author_uri);
				$media->source = new Media_Uri($c->source_name, $c->source_uri);
				$media->image = 'uploads/' . date('Ymd', strtotime($c->inserttime)) . '/' . $c->mid . '.' . $c->image;
				
				$media->thumbnail = $thumb->getThumbnail($media,	63 * 2, 95);
				
				$p->media = $media;
				$view->post = $p;
				$view->comments = $posts->findChilds($pid);
								
				if ($_GET['ajax'] == 1) {
					$this->render('thread', 'delete');
				} else {
					$this->layout('thread', 'delete');
				}
			}
		} elseif($u['grade'] >= 8 && isset($_GET['restore'])) { 
			$p = $posts->find($pid, true); // Auch gel�schte Threads suchen
			if ($p) {
				// Thread wiederherstellen
				$posts->show($pid);
				
				// Alle Kommentare wiederherstellen
				$childs = $posts->findChilds($pid, true);
				while (($c = $childs->fetch_object()) != null) {
					$posts->show($c->pid);
				}
				
				header('Location: ' . $this->view()->baseUrl() . 'thread/' . $_GET['pid'] . '/?ajax=1');
				exit;
			} else {
				// Thread existiert wirklich nicht
				return $this->noAccess();
			}
		} else {
			// Benutzer darf diesen Thread nicht l�schen
			return $this->noAccess();
		}
	}
	
	public function noAccess() {
		if ($_GET['ajax'] == 1) {
			$this->render('thread', 'noaccess');
		} else {
			$this->layout('thread', 'noaccess');
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