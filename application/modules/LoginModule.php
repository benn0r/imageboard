<?php

/**
 * LoginModule
 * 
 * @author benn0r <benjamin@benn0r.ch>
 * @since 29102011
 * @version 29102011
 */
class LoginModule extends Module
{
	
	public function run(array $args) {
		$r = $this->getRequest();
		
		if ($r->username && $r->password) {
			$users = new Users();
			$user = $users->findUser($r->username, $r->password);
			
			if ($user->num_rows > 0) {
				$_SESSION['user'] = $user->fetch_array();
			}
		}
	}
	
}