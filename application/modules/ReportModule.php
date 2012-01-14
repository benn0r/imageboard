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
 * ReportModule
 * 
 * @author benn0r <benjamin@benn0r.ch>
 * @since 2012/01/14
 * @version 2012/01/14
 */
class ReportModule extends Module
{
	
	public function run(array $args) {
		if (isset($_GET['pid'])) {
			$pid = (int)$_GET['pid'];
		}
		if (isset($_POST['pid'])) {
			$pid = (int)$_POST['pid'];
		}
		
		$posts = new Posts();
		
		if (!isset($pid) || $pid == 0) {
			return $this->notFound();
		}
		
		// user
		$u = $this->getUser();
		
		// Zu löschender Thread
		$p = $posts->find($pid);
		
		if (isset($_POST['cancel'])) {
			echo $this->view()->baseUrl() . 'thread/' . $_POST['pid'] . '/';
			return;
		}
		
		// is user allowed to report?
		if (isset($_SESSION['user'])) {
			$view = $this->view();
			$view->post = $p;
			$u = $_SESSION['user'];
			
			if (isset($_POST['message'])) {
				$reports = new Reports();
				
				$reports->insert(array(
					'uid' => $u['uid'],
					'comment' => $_POST['message'],
					'pid' => $_POST['pid'],
				));
				
				echo $this->view()->baseUrl() . 'thread/' . $_POST['pid'] . '/';
				return;
			}
			
			if (isset($_GET['ajax'])) {
				$this->render('thread', 'report');
			} else {
				$this->layout('thread', 'report');
			}
			
			return;
			
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
			} elseif (!isset($_POST['restore'])) {
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
		} else {
			// user not allowed to report
			return $this->noAccess();
		}
	}
	
	public function noAccess() {
		if (isset($_GET['ajax'])) {
			$this->render('thread', 'noaccess');
		} else {
			$this->layout('thread', 'noaccess');
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