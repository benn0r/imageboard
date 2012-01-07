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
 * UploadModule
 * 
 * This module manages new uploads and comments.
 * 
 * @author benn0r <benjamin@benn0r.ch>
 * @since 2011/11/02
 * @version 2011/12/18
 */
class UploadModule extends Module
{
	
	public function image($image) {
		$img = $image;
	
		$filetype = explode('.', $img['name']);
		$filetype = strtolower($filetype[count($filetype) - 1]);
	
		$media = new Media(new Media_Share_Plugin_Image());
		
		rename($this->view()->getConfig()->paths->cache . '/' . session_id() . '/' . md5('image') . '.' . $filetype,
				$this->view()->getConfig()->paths->cache . '/' . session_id() . '/' . md5($time = microtime(true)) . '.' . $filetype);
		
		$media->image = $this->view()->getConfig()->paths->cache . '/' . session_id() . '/' . md5($time) . '.' . $filetype;
		$media->temp = true;
		$media->filename = $img['name'];
	
		$thumb = Module::init('Thumb', $this);
		$media->thumbnail = $thumb->getThumbnail($media, 124, 93);
	
		$_SESSION['media'][] = serialize($media);
	
		$view = $this->view();
		$view->media = $media;
	
		$this->render('upload', 'image');
		return true;
	}
	
	public function share($url) {
		$services = array(
				new Media_Share_Plugin_Youtube(),
				new Media_Share_Plugin_Image(),
				new Media_Share_Plugin_Vimeo(),
				new Media_Share_Plugin_Link(),
		);
	
		foreach ($services as $service) {
			$media = $service->load($url);
			if ($media instanceof Media) {
				break;
			}
		}
	
		if ($media instanceof Media) {
			// image is only temp. saved
			$media->temp = true;
	
			if (!is_dir($this->_config->paths->cache . '/' . session_id())) {
				// create folder with this session_id
				mkdir($this->_config->paths->cache . '/' . session_id());
			}
	
			$newimage = $this->_config->paths->cache . '/' . session_id() . '/' . md5(microtime(true)) . '.' . $media->getFiletype();
			file_put_contents($newimage,
					file_get_contents($media->image));
	
			$media->image = $newimage;
	
			$view = $this->view();
			$view->media = $media;
	
			// Laden des Thumb Module
			$thumb = Module::init('Thumb', $this);
			$media->thumbnail = $thumb->getThumbnail($media, 124, 93);
	
			$_SESSION['media'][] = serialize($media);
	
			$this->render('upload', 'share');
	
			return true;
		}
	
		// Schade
		return false;
	}
	

	/**
	 * ggarciaa at gmail dot com (04-July-2007 01:57)
	 * 
	 * I needed to empty a directory, but keeping it so I slightly modified 
	 * the contribution from stefano at takys dot it (28-Dec-2005 11:57).
	 * A short but powerfull recursive function that works also if the dirs 
	 * contain hidden files.
	 * 
	 * http://www.php.net/manual/de/function.unlink.php#76186
	 * 
	 * @param string $dir the target directory
	 * @param boolean $DeleteMe if true delete also $dir, if false leave it alone
	 */
	function removeDir($dir, $DeleteMe) {
		if(!$dh = @opendir($dir)) return;
		while (($obj = readdir($dh))) {
			if($obj=='.' || $obj=='..') continue;
			if (!@unlink($dir.'/'.$obj)) $this->removeDir($dir.'/'.$obj, true);
		}
		if ($DeleteMe){
			closedir($dh);
			@rmdir($dir);
		}
	}
	
	
	/**
	 * Moves media from cachefolder to uploadsfolder
	 * 
	 * @param Media $media Media to move
	 */
	public function movePerm(Media $media) {
		$cfg = $this->getConfig();
		$dir = $cfg->paths->uploads . '/' . date('Ymd');
	
		if (!is_dir($dir)) {
			mkdir($dir);
		}
		
		// moved uploaded image
		rename($media->image, $dir . '/' . $media->mid . '.' . $media->getFiletype());
		
		// delete thumbnail, we create some new
		unlink($media->thumbnail);
	}
	
	/**
	 * If everthing is okay this method will be called
	 * and insert post and media into database
	 * 
	 * @return true
	 */
	public function createThread() {
		$r = $this->getRequest();
		$posts = new Posts();
		$pmedia = new PostsMedia();
	
		if (isset($_SESSION['media'])) {
			// take media from session
			$arrmedia = $_SESSION['media'];
		}
		
		$user = $this->getUser();
		$view = $this->view();
		
		$return = new stdClass();
		
		$this->getDb()->exec('START TRANSACTION');
		
		// insert entity in posts
		$posts->insert(array(
				'content' => $r->comment,
				
				// NULL if anonymous
				'uid' => $user ? $user['uid'] : new Database_Expression('NULL'),
				
				// NULL for thread and integer for comment
				'ppid' => $r->ppid > 0 ? (int)$r->ppid : new Database_Expression('NULL'),
				'replyto' => (int)$r->replyto,
		));
		$pid = $this->_db->lastInsertId();
	
		foreach ($arrmedia as $media) {
			$media = unserialize($media);
			
			// insert entity in mediatable
			$pmedia->insert(array(
					'pid' => $pid,
					'image' => $media->getFiletype(),
					'extid' => $media->extid,
					'name' => $media->name,
					'description' => $media->description,
					'published' => date('Y-m-d H:i:s', $media->published),
					'author_name' => $media->author ? $media->author->name : '',
					'author_uri' => $media->author ? $media->author->uri : '',
					'source_name' => $media->source ? $media->source->name : '',
					'source_uri' => $media->source ? $media->source->uri : '',
					'type' => $media->getPlugin()->id(),
					'filename' => $media->filename,
			));
	
			$media->mid = $this->_db->lastInsertId();
			$this->movePerm($media);
	
			$thumb = Module::init('Thumb', $this);
	
			$cfg = $this->getConfig();
			$dir = $cfg->paths->uploads . '/' . date('Ymd');
	
			$media->image = $dir . '/' . $media->mid . '.' . $media->getFiletype();
			$media->temp = false;
	
			$thumb->getThumbnail($media, 63 - 4, 95 - 4);
			$thumb->getThumbnail($media, 63 * 3 - 4, 95 * 3 - 4);
			$thumb->getThumbnail($media, 63 * 2 - 4, 95 - 4);
			$thumb->getThumbnail($media, 63 * 2 - 4, 95 * 2 - 4);
	
			$thumb->getThumbnail($media, 142, 206);
		}
		
		// everything is fine, go on
		$this->getDb()->exec('COMMIT');
		
		// finally delete cache folder and session data
		$this->removeDir($this->_config->paths->cache . '/' . session_id(), true);
		unset($_SESSION['media']);
		
		$return->forward = $this->view()->baseUrl() . 'thread/' . $pid;
	
		/*if ($r->ppid > 0 && $r->replyto >= 0) {
			// its a comment, load the thread
			//echo '<script type="text/javascript">loadlink(\'' . $this->view()->baseUrl() . 'thread/' . $r->ppid . '/\'); hideUpload();</script>';
			$return->forward = $this->view()->baseUrl() . 'thread/' . $r->ppid;
		} else {
			//$this->render('upload', 'form');
			//echo '<script type="text/javascript">loadlink(\'' . $this->view()->baseUrl() . '1\'); hideUpload();</script>';
		}*/
		
		echo json_encode($return);
		
		// i'm sure it worked well
		return true;
	}
	
	public function run(array $args) {
		$view = $this->view();
		$r = $this->getRequest();
		
		if (!isset($args[1])) {
			$args[1] = '';
		}
		
		if (!isset($_SESSION['media'])) {
			$_SESSION['media'] = array();
		}
		
		switch ($args[1]) {
			case 'form':
				$this->render('upload', 'uploadform');
				return;
			case 'create':
				$return = new stdClass();
				if (!isset($_SESSION['media'])) {
					$return->error = $this->getLanguage()->t('upload/errorsession');
				} elseif (count($_SESSION['media']) == 0) {
					$return->error = $this->getLanguage()->t('upload/errornomedia');
					echo json_encode($return); return;
				}
				
				
				if ($r->comment !== null && is_array($_SESSION['media'])) {
					$this->createThread();
				}
				
				return;
			case 'delmedia':
				$delete = $args[2];
				
				foreach ($_SESSION['media'] as $key => $media) {
					if ($key == $delete) {
						$media = unserialize($media);
						
						unlink($media->image);
						unlink($media->thumbnail);
						
						unset($_SESSION['media'][$key]);
					}
				}
				
				return; // finish request
			case 'setdefault':
				$default = $args[2];
				
				foreach ($_SESSION['media'] as $key => $media) {
					$media = unserialize($media);
					
					if ($key == $default) {
						$media->default = true;
					} else {
						$media->default = false;
					}
					
					$_SESSION['media'][$key] = serialize($media);
				}
				
				return; // finish request
			case 'share':
			case 'upload':
				if (!$r->url && !is_uploaded_file($_FILES['file']['tmp_name'])) {
					echo '<script type="text/javascript">parent.adderror(\'' . $this->getLanguage()->t('upload/errorbody') . '\');</script>';
					return;
				}
				
				$limit = $this->getConfig()->upload->medialimit;
				if (!$_SESSION['user'] && count($_SESSION['media']) >= $limit->anon) {
					echo '<script type="text/javascript">parent.adderror(\'' . $this->getLanguage()->t('upload/maxmediaanon') . '\');</script>';
					return;
				}
				
				if (count($_SESSION['media']) >= $limit->users) {
					echo '<script type="text/javascript">parent.adderror(\'' . $this->getLanguage()->t('upload/maxmedia') . '\');</script>';
					return;
				}
					
				if ($r->url) {
					return $this->share($this->getRequest()->url);
				} else if (is_uploaded_file($_FILES['file']['tmp_name'])) {
					// Check the upload
					if (!isset($_FILES['file']) || !is_uploaded_file($_FILES['file']['tmp_name']) || $_FILES['file']['error'] != 0) {
						$view->error = $this->getLanguage()->t('upload/errorrepeat');
						$this->render('upload', 'error');
						
						return;
					}
					
					if (exif_imagetype($_FILES['file']['tmp_name']) != IMAGETYPE_GIF &&
						exif_imagetype($_FILES['file']['tmp_name']) != IMAGETYPE_PNG &&
						exif_imagetype($_FILES['file']['tmp_name']) != IMAGETYPE_JPEG) {
						$view->error = $this->getLanguage()->t('upload/errorfile');
						$this->render('upload', 'error');
						
						return;
					}
					
					$image = $_FILES['file'];
					
					$filetype = explode('.', $_FILES['file']['name']);
					$filetype = strtolower($filetype[count($filetype) - 1]);
					
					if (!is_dir($this->view()->getConfig()->paths->cache . '/' . session_id())) {
						mkdir($this->view()->getConfig()->paths->cache . '/' . session_id());
					}
					
					move_uploaded_file($_FILES['file']['tmp_name'],
							$this->view()->getConfig()->paths->cache . '/' . session_id() . '/' . md5('image') . '.' . $filetype);
					
					return $this->image($image);
				}
		}
		
		$this->layout('upload', 'form');
	}
	
}
