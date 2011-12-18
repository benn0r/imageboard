<?php

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
	
	public function run(array $args) {
		$view = $this->view();
		
		if (isset($args[1])) switch ($args[1]) {
			case 'form':
				$this->render('upload', 'uploadform');
				return;
			case 'localfile':
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
				
				$_SESSION['upload']['image'] = $_FILES['file'];
				
				$filetype = explode('.', $_FILES['file']['name']);
				$filetype = strtolower($filetype[count($filetype) - 1]);
				
				if (!is_dir($this->view()->getConfig()->paths->cache . '/' . session_id())) {
					mkdir($this->view()->getConfig()->paths->cache . '/' . session_id());
				}
				
				move_uploaded_file($_FILES['file']['tmp_name'],
						$this->view()->getConfig()->paths->cache . '/' . session_id() . '/' . md5('image') . '.' . $filetype);
				
				return $this->image($_SESSION['upload']['image']);
		}
	}
	
}