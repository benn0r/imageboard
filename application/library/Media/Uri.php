<?php

/**
 * Media_Uri
 * 
 * @author benn0r <benjamin@benn0r.ch>
 * @since 29102011
 * @version 29102011
 */
class Media_Uri
{

	public $name;
	
	public $uri;
	
	public function __construct($name, $uri) {
		$this->name = $name;
		$this->uri = $uri;
	}

}