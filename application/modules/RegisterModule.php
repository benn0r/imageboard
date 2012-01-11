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
 * RegisterModule
 * 
 * @author benn0r <benjamin@benn0r.ch>
 * @since 2012/01/01
 * @version 2012/01/01
 */
class RegisterModule extends Module
{
	
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
	 * Called from clients which want to register an acccount
	 * 
	 * @param array $args not interesting in this module
	 */
	public function run(array $args) {
		$r = $this->getRequest();

		if (isset($_SESSION['user'])) {
			header('Location: ' . $this->view()->baseUrl());
			exit;
		}
		
		if ($this->getConfig()->register->disabled) {
			$this->layout('register', 'error');
			return;
		}
		
		if ($r->checkusername) {
			$table = new Users();
			
			$rowset = $table->findUserByName($r->checkusername);
			if ($rowset->num_rows > 0) {
				echo '1'; // account already exists
			} else {
				echo '0';
			}
			
			return;
		}
		
		/**
		 * @see http://code.google.com/intl/de-DE/apis/recaptcha/docs/php.html
		 */
		require_once('recaptchalib.php');
		
		if ($r->isPost()) {
			$table = new Users();
			
			$error = array();
			
			if (!$r->username) {
				$error['username'] = true;
			}
			
			if (!$r->password) {
				$error['password'] = true;
			}
			
			if ($this->getConfig()->register->captcha && !isset($_SESSION['proved_as_a_human'])) {
				$resp = recaptcha_check_answer($this->getConfig()->captcha->privatekey,
					$r->getServer('REMOTE_ADDR'),
					$r->recaptcha_challenge_field,
					$r->recaptcha_response_field);
				
				if (!$resp->is_valid) {
					$error['captcha'] = true;
				} elseif ($this->getConfig()->register->captcha_once == 1) {
					$_SESSION['proved_as_a_human'] = true;
				}
			}
			
			if ($r->password && $r->password != $r->passwordrepeat) {
				$error['password'] = true;
			}
			
			$rowset = $table->findUserByName($r->username);
			if ($rowset->num_rows > 0) {
				$error['username'] = $this->getLanguage()->t('register/invalidusername');
			}
			
			if (count($error) == 0) {
				// everything okay
				$table->insert(array(
					'username' => htmlspecialchars($r->username, ENT_QUOTES),
					'password' => md5($r->password),
					'email' => htmlspecialchars($r->email, ENT_QUOTES),
						
					'grade' => 1,
					'status' => 1,
				));
				
				$this->layout('register', 'success');
				return;
			}
			
			$this->view()->error = $error;
		}
		
		$this->view()->r = $this->getRequest();
		$this->layout('register', 'form');
	}
	
}