<?php

/**
 * UploadModule
 * 
 * @author benn0r <benjamin@benn0r.ch>
 * @since 02112011
 * @version 02112011
 */
class UploadModule extends Module
{
	
	public function image($fileid) {		
		$img = $_SESSION['upload']['image'];
		
		$filetype = explode('.', $img['name']);
		$filetype = strtolower($filetype[count($filetype) - 1]);
		
		$media = new Media(new Media_Share_Plugin_Image());
		$media->image = $this->view()->getConfig()->paths->cache . '/' . session_id() . '/' . md5('image') . '.' . $filetype;
		$media->temp = true;
		$media->filename = $img['name'];
		
		$thumb = Module::init('Thumb', $this);
		$media->thumbnail = $thumb->getThumbnail($media, 124, 93);
		
		$_SESSION['media'] = serialize($media);
		
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
			// Das Bild soll in den Cache Ordner
			$media->temp = true;
			
			if (!is_dir($this->_config->paths->cache . '/' . session_id())) {
				// Existiert noch kein Ordner für dieses Bild legen wir den mal an
				mkdir($this->_config->paths->cache . '/' . session_id());
			}
			
			$newimage = $this->_config->paths->cache . '/' . session_id() . '/' . md5('image') . '.' . $media->getFiletype();
			file_put_contents($newimage,
				file_get_contents($media->image));
				
			$media->image = $newimage;

			$view = $this->view();
			$view->media = $media;
			
			// Laden des Thumb Module
			$thumb = Module::init('Thumb', $this);
			$media->thumbnail = $thumb->getThumbnail($media, 124, 93);
			
			$_SESSION['media'] = serialize($media);
			
			$this->render('upload', 'share');
			
			return true;
		}
		
		// Schade
		return false;
	}
	
	public function movePerm(Media $media) {
		$cfg = $this->getConfig();
		$dir = $cfg->paths->uploads . '/' . date('Ymd');
		
		if (!is_dir($dir)) {
			mkdir($dir);
		}

		rename($media->image, $dir . '/' . $media->mid . '.' . $media->getFiletype());
	}
	
	public function run(array $args) {
		$user = $this->getUser();
		$db = $this->getDb();
		$view = $this->view();
		
		$share = $this->getRequest()->share;
		if ($share) {
			return $this->share($this->getRequest()->url);
		}
		
		if ($this->getRequest()->image) {
			return $this->image($this->getRequest()->fileid);
		}
		
		if ($this->getRequest()->form != null) {
			return $this->render('upload', 'form');
		}
		
		if ($this->getRequest()->swf == 1) {
		
			// Get the session Id passed from SWFUpload. We have to do this to work-around the Flash Player Cookie Bug
			if (isset($_POST["PHPSESSID"])) {
				session_id($_POST["PHPSESSID"]);
			}
		
			ini_set("html_errors", "0");
		
			// Check the upload
			if (!isset($_FILES["Filedata"]) || !is_uploaded_file($_FILES["Filedata"]["tmp_name"]) || $_FILES["Filedata"]["error"] != 0) {
				echo "ERROR:invalid upload";
				exit(0);
			}
			
			$_SESSION['upload']['image'] = $_FILES["Filedata"];
			
			$filetype = explode('.', $_FILES["Filedata"]["name"]);
			$filetype = strtolower($filetype[count($filetype) - 1]);
			
			if (!is_dir($this->view()->getConfig()->paths->cache . '/' . session_id())) {
				mkdir($this->view()->getConfig()->paths->cache . '/' . session_id());
			}
			
			move_uploaded_file($_FILES["Filedata"]["tmp_name"], 
				$this->view()->getConfig()->paths->cache . '/' . session_id() . '/' . md5('image') . '.' . $filetype);
		
			echo "FILEID:" . session_id();	// Return the file id to the script
			exit;
		}
		
		$r = $this->getRequest();
		if (($this->getRequest()->comment !== null && isset($_SESSION['media'])) ||
			($r->ppid >= 0 && $r->replyto >= 0)) {
			$posts = new Posts();
			$pmedia = new PostsMedia();
			
			if (isset($_SESSION['media'])) {
				$media = unserialize($_SESSION['media']);
			}
			
			$posts->insert(array(
				'content' => $r->comment,
				'uid' => $this->view()->user['uid'],
				'ppid' => $r->ppid >= 0 ? (int)$r->ppid : NULL,
				'replyto' => (int)$r->replyto,
			));
			$pid = $this->_db->lastInsertId();
			
			if (isset($media)) {
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
			
			unset($_SESSION['media']);
			
			if ($r->ppid >= 0 && $r->replyto >= 0) {
				echo '<script type="text/javascript">loadlink(\'' . $this->view()->baseUrl() . 'thread/' . $r->ppid . '/\');</script>';
			} else {
				$this->render('upload', 'form');
				echo '<script type="text/javascript">loadlink(\'' . $this->view()->baseUrl() . '1\');</script>';
			}
			
			exit;
		}
	}
	
}