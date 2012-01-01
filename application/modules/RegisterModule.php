<?php

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

		if ($_SESSION['user']) {
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
		
		if ($r->username && $r->password) {
			$table = new Users();
			
			if ($this->getConfig()->register->captcha) {
				$resp = recaptcha_check_answer($this->getConfig()->captcha->privatekey,
					$r->getServer('REMOTE_ADDR'),
					$r->recaptcha_challenge_field,
					$r->recaptcha_response_field);
				
				if (!$resp->is_valid) {
					$this->view()->error = $this->getLanguage()->t('register/invalidcaptcha');
				}
			}
			
			if ($r->password && $r->password != $r->passwordrepeat) {
				$this->view()->error = $this->getLanguage()->t('register/invalidpassword');
			}
			
			$rowset = $table->findUserByName($r->username);
			if ($rowset->num_rows > 0) {
				$this->view()->error = $this->getLanguage()->t('register/invalidusername');
			}
			
			if (!$this->view()->error) {
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
		}
		
		$this->view()->r = $this->getRequest();
		$this->layout('register', 'form');
	}
	
}