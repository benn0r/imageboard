<?php

/**
 * Media_Share_Plugin
 * 
 * @author benn0r <benjamin@benn0r.ch>
 * @since 05112011
 * @version 05112011
 */
class Media_Share_Plugin_Link implements Media_Share_Plugin
{
	
	public function id() {
		2;
	}
	
	public function name() {
		return 'Link';
	}
	
	public function isVideo() {
		return false;
	}

	public function load($url) {
	
		$headers = get_headers($url);
		foreach ($headers as $key => $header) {
			if (strstr($header, 'Content-Type:') && !strstr($header, 'text/html')) {
				return false;
			} elseif (strstr($header, 'Last-Modified')) {
				$header = str_replace('Last-Modified:', '', $header);
				$published = strtotime(trim($header));
			}
		}
		
		$purl = parse_url($url);
		$imgurl = 'http://' . $purl['host'];
		$path = explode('/', $purl['path']);
		unset($path[count($path) - 1]);
		$imgurl .= implode('/', $path) . '/';
		
		$contents = file_get_contents($url);
		
		
		$doc = new DOMDocument();
		@$doc->loadHTML($contents);
		$tags = $doc->getElementsByTagName('img');
		
		foreach ($tags as $tag) {
			if (!strstr($tag->getAttribute('src'), 'pixel')) {
				//$headers = get_headers($imgurl . $tag->getAttribute('src'));
				//print_r($headers);
				if (strstr($tag->getAttribute('src'), 'http://')) {
					echo '<img src="' . $tag->getAttribute('src') . '" /><br />';
				} else {
					echo '<img src="' . $imgurl . $tag->getAttribute('src') . '" /><br />';
				}
			}
		}
		
	}

}