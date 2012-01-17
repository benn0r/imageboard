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
		
		setcookie('remember', '', 0, '/');
		
		// forward to homepage
		header('Location: ' . $this->view()->baseUrl());
		exit;
	}
	
}