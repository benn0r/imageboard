<?php

/**
 * IndexModule
 * 
 * @author benn0r <benjamin@benn0r.ch>
 * @since 29102011
 * @version 29102011
 */
class Helper extends Application_Helper
{
	
	/**
	 * @param string $module
	 */
	public function getModule($module) {
		switch ($module) {
			case 'notifications':
				return 'Notifications';
			case 'res':
				return 'Ressource';
			case 'user':
				return 'User';
			case 'login':
				return 'Login';
			case 'logout':
				return 'Logout';
			case 'import':
				return 'Import';
			case 'upload':
				return 'Upload';
			case 'shoutbox':
				return 'Shoutbox';
			case 'thread':
				return 'Thread';
			case 'delete':
				return 'Delete';
			default:
				if ($this->isBoard($module)) {
					return 'Board';
				}
				return 'Index';
				
		}
	}
	
	public function isBoard($module) {
		$db = $this->_application->getDb();
		
		$result = $db->select('SELECT * FROM board_boards
			WHERE pbid > 0 AND url_alias = "' . $db->escape($module) . '"');
		
		if ($result->num_rows > 0) {
			return true;
		}
		
		// Kein Board
		return false;
	}
	
}