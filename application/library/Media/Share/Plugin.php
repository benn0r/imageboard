<?php

/**
 * Media_Share_Plugin
 * 
 * @author benn0r <benjamin@benn0r.ch>
 * @since 05112011
 * @version 05112011
 */
interface Media_Share_Plugin
{

	/**
	 * Methode die f�r das einlesen eines neuen
	 * Mediums zust�ndig ist.
	 * 
	 * @param $url Eingegebene Url
	 * @return Media
	 */
	public function load($url);
	
	public function name();
	
	public function isVideo();
	
	public function id();

}