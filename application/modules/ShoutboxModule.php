<?php

/**
 * ShoutboxModule
 * 
 * @author benn0r <benjamin@benn0r.ch>
 * @since 29102011
 * @version 29102011
 */
class ShoutboxModule extends Module
{
	
	public function run(array $args) {
		$this->render('main', 'shoutbox');
	}
	
}