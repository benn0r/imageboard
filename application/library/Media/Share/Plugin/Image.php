<?php

/**
 * Media_Share_Plugin
 * 
 * @author benn0r <benjamin@benn0r.ch>
 * @since 05112011
 * @version 05112011
 */
class Media_Share_Plugin_Image implements Media_Share_Plugin
{

	private $_patterns = array(
		'/(.*)\.(jpg|jpeg|png|gif)(\?){0,1}(.*)/',
	);
	
	public function id() {
		1;
	}
	
	public function name() {
		return 'Grafik';
	}
	
	public function isVideo() {
		return false;
	}

	public function load($url) {
		$img = false;

		// Hier wird das erste mal Anhand der URL geprüft 
		// ob es ein Bild sein könnte
		foreach ($this->_patterns as $pattern) {
			$matches = array();
			preg_match($pattern, $url, $matches);
			if (count($matches) > 0) {
				$img = true;
			}
		}
		
		if (!$img) {
			// Url ist kein Bild
			return false;
		}
		
		// Hier erfolgt der zweite Check, dabei fragen wir den
		// Server direkt nach dem Bild und werten den HTTP Header,
		// insbesondere natürlich, den Content-Type aus.
		$headers = get_headers($url);
		foreach ($headers as $key => $header) {
			if (strstr($header, 'Content-Type')) {
				$header = str_replace('Content-Type:', '', $header);
				switch (trim($header)) {
					case 'image/jpeg':
					case 'image/png':
					case 'image/gif':
						break;
					default:
						// Kein gültiges Bild
						return false;
				}
			} elseif (strstr($header, 'Last-Modified')) {
				$header = str_replace('Last-Modified:', '', $header);
				$published = strtotime(trim($header));
			}
		}
		
		$media = new Media($this);
		
		$media->image = $url;
		if (isset($published)) {
			$media->published = $published;
		}
		
		$arr = parse_url($url);
		if ($arr) {
			$media->source = new Media_Uri($arr['host'], 'http://' . $arr['host']);
		}
		
		return $media;
		
	}
	
}