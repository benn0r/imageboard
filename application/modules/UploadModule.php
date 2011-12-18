<?php

/**
 * UploadModule
 * 
 * This module manages new uploads and comments.
 * 
 * @author benn0r <benjamin@benn0r.ch>
 * @since 2011/11/02
 * @version 2011/12/18
 */
class UploadModule extends Module
{
	
	public function run(array $args) {
		if (isset($args[1])) switch ($args[1]) {
			case 'localfile':
				print_r($_FILES);
				break;
		}
	}
	
}