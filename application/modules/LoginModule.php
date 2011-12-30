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
			$this->layout('login', 'error');
		}
		
		// @todo errorpage or loginbox if no logindata received
		header('Location: ' . $this->view()->baseUrl());
		exit;
	}
	
}