<?php

/**
 * Printspecial
 * 
 * @author benn0r <benjamin@benn0r.ch>
 * @since 30102011
 * @version 30102011
 */
class Printspecial
{
	
	static protected function rainbow($text, $enclose = '%s') {
	    /*** initialize the return string ***/
	    $ret = '';
	
	    /*** an array of colors ***/
	    $colors = array(
	        'ff00ff',
	        'ff00cc',
	        'ff0099',
	        'ff0066',
	        'ff0033',
	        'ff0000',
	        'ff3300',
	        'ff6600',
	        'ff9900',
	        'ffcc00',
	        '99ff00',
	        '66ff00',
	        '33ff00',
	        '00ff00',
	        '00ff33',
	        '00ff66',
	        '00ff99',
	        '00ffcc',
	        '00ffff',
	        '00ccff',
	        '0099ff',
	        '0066ff',
	        '0033ff',
	        '0000ff',
	        '3300ff',
	        '6600ff',
	        '9900ff',
	        'cc00ff');
	    /*** a counter ***/
	    $i = 0;
	
	    /*** get the length of the text ***/
	    $textlength = strlen($text);
	
	    /*** loop over the text ***/
	    while($i<=$textlength)
	    {
	        /*** loop through the colors ***/
	        foreach($colors as $value)
	        {
	            if ($text[$i] != "" && $text[$i] != "\n")
	            {
	                $ret .= '<span style="color:#'.$value.';">'.sprintf($enclose, $text[$i])."</span>";
	            }
	        $i++;
	        }
	    }
	    /*** return the highlighted string ***/
	    return $ret;
	}
	
	static public function postclass($post) {
		if (date('md', strtotime($post->birthday)) == date('md')) {
			//return 'birthday';
		}
		return '';
	}
	
	static public function post($post) {
		return $post->content;
		
		if (date('md', strtotime($post->birthday)) == date('md')) {
			$age = date('Y') - date('Y', strtotime($post->birthday));
			
			/*return '<strong>' . Printspecial::rainbow('Today i went ' . $age . '!!!', '<b>%s</b>') . '</strong>
				<br />' . $post->content;*/
			
			return '<span class="birthdaypost">' . Printspecial::rainbow($post->content) . '</span>';
		}
		return $post->content;
	}
	
	static public function wrap($content, $url = '') {
		$words = explode(' ', $content);
		$new = '';
		foreach ($words as $word) {
			$new .= wordwrap($word, 68, '<br />', true) . ' ';
		}
		
		$new = preg_replace('#http://(.*?)\s#i', '<a href="http://$1" rel="nofollow" target="_blank">$1</a>', $new);
		return preg_replace('/\@\[(.*?)\:(.*?)\]/', '<a href="' . $url . 'user/$1" onclick="return loadpage(this)">$2</a>', $new);
	}

}