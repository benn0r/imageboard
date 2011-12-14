<?php

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
				
				// forwarding to homepage
				// @todo forwarding to last page
				header('Location: ' + BASE_URL);
			}
			
			// @todo errorpage if login is invalid
		}
		
		// @todo errorpage or loginbox if no logindata received
	}
	
}