<?php

/**
 * Media_Share_Plugin
 * 
 * @author benn0r <benjamin@benn0r.ch>
 * @since 05112011
 * @version 05112011
 */
class Media_Share_Plugin_Youtube implements Media_Share_Plugin
{

	/**
	 * Array mit allen Pattern die für Youtube Videos möglich
	 * sind. Diese Liste wird vermutlich nicht alle Links
	 * abdecken können, allerdings die wichtigstens Links sind
	 * vorhanden.
	 * 
	 * @var array<int>
	 */
	private $_patterns = array(
		'/youtube.com\/(.*?)v=([a-zA-Z0-9-_]*)/' => 2,
		'/youtu.be\/([a-zA-Z0-9-_]*)/' => 1,
	);
	
	/**
	 * Die URL zur Youtube API
	 * 
	 * @var string
	 */
	private $_infourl = 'http://gdata.youtube.com/feeds/api/videos/%s?alt=json';
	
	public function id() {
		return 4;
	}
	
	public function name() {
		return 'Video';
	}
	
	public function isVideo() {
		return true;
	}

	/**
	 * Liest alle relevanten Informationen eines Youtube Videos ein
	 * und speichert diese in einem Media Objekt.
	 * 
	 * @param $url URL zum Video
	 * @return Media
	 */
	public function load($url) {
		$v = null;
	
		foreach ($this->_patterns as $pattern => $key) {
			$matches = array();
			preg_match($pattern, $url, $matches);
			if (count($matches) > 0) {
				// Übereinstimmung mit dem Pattern. In $key steht die Position
				// des relevanten Strings im Resultat von preg_match
				$v = $matches[$key];
			}
		}
		
		if ($v === null) {
			// URL ist kein Youtube Video
			return false;
		}
		
		$media = new Media($this);
		
		// Standard URL zum Video Thumbnail, diese URL steht zwar auch in
		// in der API aber so gehts einfacher und funzt ebenso gut
		$media->image = 'http://i.ytimg.com/vi/' . $v . '/0.jpg';
		
		$contents = json_decode(file_get_contents(sprintf($this->_infourl, $v)));
		
		// Der Name der Variable hat ein $ im Namen, deshalb müssen
		// wir diesen kleinen Trick anwenden 
		$paramname = '$t';
		
		$media->extid = $v;
		$media->name = $contents->entry->title->$paramname;
		$media->description = utf8_decode($contents->entry->content->$paramname);
		$media->published = strtotime($contents->entry->published->$paramname);
		
		$username = $contents->entry->author[0]->name->$paramname;
		
		$media->author = new Media_Uri($username,
			'http://www.youtube.com/' . $username);
		
		$media->source = new Media_Uri('Youtube', 'http://www.youtube.com');
		
		// Auslesen aller Tags
		foreach ($contents->entry->category as $tag) {
			// Kategorie können wir auch als Tag nehmen
			if (strstr($tag->scheme, 'categories') || strstr($tag->scheme, 'keywords')) {
				$media->tags[] = $tag->term;
			}
		}
		
		return $media;
	}

}