<?php

/**
 * Copyright (c) 2012 benn0r <benjamin@benn0r.ch>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

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
			case 'search':
				return 'Search';
			case 'user':
				return 'User';
			case 'login':
				return 'Login';
			case 'logout':
				return 'Logout';
			case 'settings':
				return 'Settings';
			case 'password':
				return 'Password';
			case 'register':
				return 'Register';
			case 'import':
				return 'Import';
			case 'upload':
				return 'Upload';
			case 'shoutbox':
				return 'Shoutbox';
			case 'thread':
				return 'Thread';
			case 'rating':
				return 'Rating';
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