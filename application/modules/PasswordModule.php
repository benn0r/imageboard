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
 * PasswordModule
 * 
 * Allows to send new password to users
 * 
 * @author benn0r <benjamin@benn0r.ch>
 * @since 2011/12/30
 * @version 2011/12/30
 */
class PasswordModule extends Module
{
	
	/**
	 * Generate an easy password with vocals
	 * and consonants
	 * 
	 * Source: Book ISBN 978-3-8362-1139-0, 2009
	 * 
	 * @param int $length length * 2 is the real length
	 * @return string password
	 */
	public function mkpassword($length = 3) {
		mt_srand(microtime(true) * 1000000);
		
		// vocals
		$vocs = array('a', 'e', 'i', 'o', 'u');
		
		// consonants
		$cons = array('b', 'c', 'd', 'f', 'g', 'h', 'j',
					  'k', 'l', 'm', 'n', 'p', 'q', 'r',
					  's', 't', 'v', 'w', 'x', 'y', 'z');
		
		$vocmax = count($vocs) - 1;
		$conmax = count($cons) - 1;
		
		$password = '';
		for ($i = 0; $i < $length; $i++) {
			$password .= $vocs[mt_rand(0, $vocmax)];
			$password .= $cons[mt_rand(0, $conmax)];
		}
		
		return $password;
	}
	
	public function sendEmail($username, $email, $password) {
		$c = $this->getConfig()->contact;
		$t = $this->getLanguage();
		
		$mail = new PHPMailer();
		$mail->SetFrom($c->email, $c->name);
		$mail->AddAddress($email, $username);
		$mail->Subject = $t->t('password/subject');				
		$mail->MsgHTML(sprintf($t->t('password/email'), $username, $password, $c->websitename));
		
		// test!!
		$mail->IsSMTP(true);
		$mail->SMTPAuth = true;
		$mail->SMTPSecure = 'ssl'; // gmail needs that
		$mail->Host = 'smtp.gmail.com';
		$mail->Username = '';
		$mail->Password = '';
		$mail->Port = 465;
		
		$mail->Send();
	}
	
	/**
	 * Called from clients which want to login
	 * 
	 * @param array $args not interesting in this module
	 */
	public function run(array $args) {
		$r = $this->getRequest();

		if (isset($_SESSION['user'])) {
			header('Location: ' . $this->view()->baseUrl());
			exit;
		}
		
		if ($r->email) {
			$users = new Users();
			
			$rowset = $users->findUserbyEmail($r->email);
			if ($rowset->num_rows > 0) {
				// user found
				$user = $rowset->fetch_object();
				$password = $this->mkpassword(4);
				
				$users->updatePassword($user->uid, $password); // save new password
				$this->sendEmail($user->username, $user->email, $password); // send email with password
				
				$this->layout('password', 'success');
				return;
			} else {
				// sorry, no user found
				$this->layout('password', 'error');
				return;
			}
		}
		
		if ($r->isPost()) {
			$this->view()->error = true;
		}
		
		$this->layout('password', 'form');
	}
	
}