<?php

/**
 * LogoutModule
 * 
 * @author benn0r <benjamin@benn0r.ch>
 * @since 2011/12/30
 * @version 2011/12/30
 */
class LogoutModule extends Module
{
	
	/**
	 * Called from clients which want to logout
	 * 
	 * @param array $args not interesting in this module
	 */
	public function run(array $args) {
		$r = $this->getRequest();
		
		// kill the session!
		session_destroy();
		
		// forward to homepage
		header('Location: ' . $this->view()->baseUrl());
		exit;
	}
	
}