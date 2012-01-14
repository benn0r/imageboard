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
 * @since 29102011
 * @version 29102011
 */
class DeleteModule extends Module
{
	
	public function run(array $args) {
		if (isset($_GET['pid'])) {
			$pid = (int)$_GET['pid'];
		}
		if (isset($_POST['pid'])) {
			$pid = (int)$_POST['pid'];
		}
		
		$posts = new Posts();
		
		if ($pid == 0) {
			return $this->notFound();
		}
		
		// Eingeloggter Benutzer
		$u = $this->getUser();
		
		// Zu löschender Thread
		$p = $posts->find($pid);
		
		// Rechte überprüfen
		if ($p && is_array($u) && ($u['uid'] == $p->uid || $u['grade'] >= 8)) {
			// Benutzer ist Besitzer oder mindestens Moderator
			
			if (isset($_POST['delete'])) {
				// Thread löschen
				$posts->hide($pid);
				
				// Alle Kommentare löschen
				$childs = $posts->findChilds($pid);
				while (($c = $childs->fetch_object()) != null) {
					$posts->hide($c->pid);
				}
				
				if ($u['grade'] >= 8) {
					echo $this->view()->baseUrl() . 'thread/' . $_POST['pid'] . '/'; return;
				} else {
					echo $this->view()->baseUrl(); return;
				}
				exit;
			} elseif (isset($_POST['cancel'])) {
				echo $this->view()->baseUrl() . 'thread/' . $_POST['pid'] . '/'; return;
				exit;
			} elseif (!isset($_GET['restore'])) {
				$thumb = Module::init('Thumb', $this);
				$view = $this->view();
				
				$view->post = $p;
				$view->comments = $posts->findChilds($pid);
				
				$rowset = $posts->fetchMedia($p->pid, isset($_SESSION['user']) && $_SESSION['user']['grade'] >= 8 ? true : false);
				
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
				
					$media->thumbnail = $thumb->getThumbnail($media, 90, 90);
				
					$allmedia[] = $media;
				}
				$view->media = $allmedia;
								
				if (isset($_GET['ajax'])) {
					$this->render('thread', 'delete');
				} else {
					$this->layout('thread', 'delete');
				}
			}
		} elseif($u['grade'] >= 8 && isset($_GET['restore'])) { 
			$p = $posts->find($pid, true); // Auch gelöschte Threads suchen
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
			// Benutzer darf diesen Thread nicht löschen
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