<?php

/**
 * Printdate
 * 
 * @author benn0r <benjamin@benn0r.ch>
 * @since 29102011
 * @version 29102011
 */
class Printsize
{
	
	/**
	 * Source: http://php.net/manual/de/function.filesize.php stachu540@gmail.com
	 * 
	 * @param unknown_type $file
	 * @param unknown_type $setup
	 * @return string|string|string
	 */
	static public function filesize($file, $setup = null) {
	    $FZ = ($file && @is_file($file)) ? filesize($file) : NULL;
	    $FS = array("B","KB","MB","GB","TB","PB","EB","ZB","YB");
	   
	    if(!$setup && $setup !== 0) {
	        return number_format($FZ/pow(1024, $I=floor(log($FZ, 1024))), ($i >= 1) ? 2 : 0) . $FS[$I];
		} elseif ($setup == 'INT') {
			return number_format($FZ); 
		} else {
			return number_format($FZ/pow(1024, $setup), ($setup >= 1) ? 2 : 0 ). ' ' . $FS[$setup];
		}
	}

}