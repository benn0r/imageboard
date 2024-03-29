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
 * SettingsModule
 * 
 * @author benn0r <benjamin@benn0r.ch>
 * @since 2012/01/04
 * @version 2011/01/04
 */
class SettingsModule extends Module
{
	
	public $languages = array('de', 'en');
	
	public function run(array $args) {		
		// init
		$user = $this->getUser();
		$r = $this->getRequest();
		
		if (!isset($_SESSION['user'])) {
			// user needs to be loggedin, sorry
			header('Location: ' . $this->view()->baseUrl());
			exit;
		}

		// update useractivity
		if (is_array($user = $this->getUser()) && $user['grade'] > 0) {
			Users::setactive($user, array('settings'));
		}
		
		$view = $this->view();
		$view->a = isset($args[1]) ? $args[1] : '';
		$view->r = $r;
		
		$users = new Users();
		
		switch ($view->a) {
			case 'login':
				if ($r->isPost()) {
					if ($r->password != $r->passwordrepeat) {
						$view->error = true;
					} else {
						$users->update(array(
								'email' => $r->email,
								'password' => md5($r->password),
						), 'uid = ' . $user['uid']);
					}
				}
				
				$view->login = $users->find($user['uid']);
				break;
			case 'avatar':
				if (isset($_FILES) && isset($_FILES['avatar'])) {
					if (!$_FILES['avatar']['tmp_name']) {
						$view->error = true;
					} else {
						if (exif_imagetype($_FILES['avatar']['tmp_name']) != IMAGETYPE_GIF &&
								exif_imagetype($_FILES['avatar']['tmp_name']) != IMAGETYPE_PNG &&
								exif_imagetype($_FILES['avatar']['tmp_name']) != IMAGETYPE_JPEG) {
							$view->error = true;
						}
						
						$filetype = explode('.', $_FILES['avatar']['name']);
						$filetype = strtolower($filetype[count($filetype) - 1]);
						
						if (!$view->error) {
							unlink($this->view()->getConfig()->paths->avatars . '/' . $user['uid'] . '.' . $user['avatar']);
							
							move_uploaded_file($_FILES['avatar']['tmp_name'],
									$this->view()->getConfig()->paths->avatars . '/' . $user['uid'] . '.' . $filetype);
							
							$users->update(array(
									'avatar' => $filetype,
							), 'uid = ' . $user['uid']);
							
							// reload session
							$_SESSION['user']['avatar'] = $filetype;
							$view->user = $_SESSION['user'];
						}
					}
				}
				
				break;
			case 'notifications':
				if ($r->isPost()) {
					$users->update(array(
							'notification_own' => (int)$r->notification_own,
							'notification_other' => (int)$r->notification_other,
							'notification_wall' => (int)$r->notification_wall,
							'notification_own_mail' => (int)$r->notification_own_mail,
							'notification_other_mail' => (int)$r->notification_other_mail,
							'notification_wall_mail' => (int)$r->notification_wall_mail,
					), 'uid = ' . $user['uid']);
				}
				
				$view->notifications = $users->find($user['uid']);
				break;
			case 'design':
				if ($r->isPost() && ($r->design == 'default' || $r->design == 'classic')) {
					$users->update(array(
							'design' => $r->design,
					), 'uid = ' . $user['uid']);
					
					$_SESSION['user']['design'] = $r->design;
				}
				
				$view->design = $users->find($user['uid']);
				break;
			case 'language':
				if ($r->isPost() && in_array($r->language, $this->languages)) {
					$users->update(array(
							'language' => $r->language,
					), 'uid = ' . $user['uid']);
				
					$_SESSION['user']['language'] = $r->language;
				}
				
				$view->lang = $users->find($user['uid']);
				break;
				break;
			case 'profile':
			default:
				$view->a = 'profile';
				
				if ($r->isPost()) {
					$users->update(array(
							'homepage' => $r->homepage,
							'hobbies' => $r->hobbies,
							'sex' => (int)$r->sex,
							'birthday' => $r->birthday ? date('Y-m-d', strtotime($r->birthday)) : '0000-00-00',
					), 'uid = ' . $user['uid']);
					
					if (isset($_FILES) && isset($_FILES['header'])) {
						if (!$_FILES['header']['tmp_name']) {
							$view->error = true;
						} else {
							if (exif_imagetype($_FILES['header']['tmp_name']) != IMAGETYPE_GIF &&
									exif_imagetype($_FILES['header']['tmp_name']) != IMAGETYPE_PNG &&
									exif_imagetype($_FILES['header']['tmp_name']) != IMAGETYPE_JPEG) {
								$view->error = true;
							}
					
							$filetype = explode('.', $_FILES['header']['name']);
							$filetype = strtolower($filetype[count($filetype) - 1]);
					
							if (!$view->error) {
								@unlink($this->view()->getConfig()->paths->headers . '/' . $user['uid'] . '.' . $user['header']);
					
								move_uploaded_file($_FILES['header']['tmp_name'],
										$this->view()->getConfig()->paths->headers . '/' . $user['uid'] . '.' . $filetype);
					
								$users->update(array(
										'header' => $filetype,
								), 'uid = ' . $user['uid']);
					
								// reload session
								$_SESSION['user']['header'] = $filetype;
								$view->user = $_SESSION['user'];
							}
						}
					}
				}
			
				$view->profile = $users->find($user['uid']);
				break;
		}
		
		if (isset($_GET['ajax'])) {
			$this->render('settings', 'form');
		} else {
			$this->layout('settings', 'form');
		}
	}
	
}