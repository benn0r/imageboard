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
 * LoginModule
 * 
 * @author benn0r <benjamin@benn0r.ch>
 * @since 2011/10/29
 * @version 2011/10/29
 */
class LoginModule extends Module
{
	
	/**
	 * Called from clients which want to login
	 * 
	 * @param array $args not interesting in this module
	 */
	public function run(array $args) {
		$r = $this->getRequest();

		if ($r->username && $r->password) {
			$users = new Users();
			$user = $users->findUser($r->username, $r->password);
			
			if ($user->num_rows > 0) {
				$_SESSION['user'] = $user->fetch_array();
				
				echo '1';
				return;
				
				// forward to last page
				if (isset($_SESSION['login_forward'])) {
					header('Location: ' . $_SESSION['login_forward']);
					exit;
				} else {
					header('Location: ' . $r->forward);
					exit;
				}
			}
			
			// store last page for later use
			$_SESSION['login_forward'] = $r->forward;
			
			// show errorbox
			//$this->layout('login', 'error');
			echo '0';
			return;
		}
		
		// @todo errorpage or loginbox if no logindata received
		header('Location: ' . $this->view()->baseUrl());
		exit;
	}
	
}