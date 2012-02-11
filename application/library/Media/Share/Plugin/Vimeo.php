<?php

/**
 * Media_Share_Plugin
 * 
 * @author benn0r <benjamin@benn0r.ch>
 * @since 05112011
 * @version 05112011
 */
class Media_Share_Plugin_Vimeo implements Media_Share_Plugin
{

	private $_patterns = array(
		'/vimeo.com\/([0-9]*)/' => 1,
	);
	
	public function id() {
		return 3;
	}
	
	public function name() {
		return 'Video';
	}
	
	public function isVideo() {
		return true;
	}

	public function load($url) {
		$id = null;
	
		foreach ($this->_patterns as $pattern => $key) {
			$matches = array();
			preg_match($pattern, $url, $matches);
			if (count($matches) > 0) {
				$id = $matches[$key];
			}
		}
		
		if ($id === null) {
			// URL ist kein Youtube Video
			return false;
		}
		
		$vimeo = file_get_contents('http://vimeo.com/api/v2/video/'.$id.'.xml');
		$media = new Media($this);
		
		$arr = array();
		preg_match('/<thumbnail_large>(.*)<\/thumbnail_large>/', $vimeo, $arr);
		
		if (!count($arr)) {
			// Hier checken wir noch kurz ob in dem XML File wirklich die
			// Daten sind die wir erwarten. Wenn kein Thumbnail gefunden wird
			// können wir von einen ungültigen Video ID ausgehen und abbrechen
			return false;
		}
		
		$media->image = $arr[1];
		
		$media->extid = $id;
		
		$arr = array();
		preg_match('/<title>(.*)<\/title>/', $vimeo, $arr);
		$media->name = $arr[1];
		
		$arr = array();
		preg_match('/<description>(.*)<\/description>/is', $vimeo, $arr);
		$media->description = $arr[1];
		
		$arr = array();
		preg_match('/<upload_date>(.*)<\/upload_date>/', $vimeo, $arr);
		$media->published = strtotime($arr[1]);
		
		$arr = array();
		preg_match('/<tags>(.*)<\/tags>/', $vimeo, $arr);
		$media->tags = explode(', ', $arr[1]);
		
		$arr = array();
		preg_match('/<user_name>(.*)<\/user_name>/', $vimeo, $arr);
		$username = $arr[1];
		
		$arr = array();
		preg_match('/<user_url>(.*)<\/user_url>/', $vimeo, $arr);
		$userurl = $arr[1];
		
		$media->author = new Media_Uri($username, $userurl);
		
		$media->source = new Media_Uri('Vimeo', 'http://www.vimeo.com');
		
		return $media;
	}

}