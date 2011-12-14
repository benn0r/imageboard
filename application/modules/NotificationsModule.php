<?php

/**
 * NotificationsModule
 * 
 * Clients continuous access this module. It delivers notifications,
 * list with active users and news. It requires a timestamp
 * to find newer entries than that timestamp.
 * 
 * @author benn0r <benjamin@benn0r.ch>
 * @since 2011/12/10
 * @version 2011/12/14
 */
class NotificationsModule extends Module
{
	
	/**
	 * Returns an json compatible error object
	 * 
	 * @param int $error
	 * @return stdClass error object
	 */
	protected function error($error) {
		$obj = new stdClass();
		$obj->error = 1;
		$obj->message = $error;
		
		return $obj;
	}
	
	/**
	 * Generates an array with all active users
	 * 
	 * @param Users $users Users model
	 * @return array all active users
	 */
	protected function users(Users $users) {
		$r = array();
		$active = $users->active();
		
		while (($u = $active->fetch_object()) != null) {
			$uobj = new stdClass();
			$uobj->uid = $u->uid;
			$uobj->username = $u->username;
			
			// generate avatar url
			$uobj->avatar = $this->view()->baseUrl() . $this->getConfig()->paths->avatars . '/' . $u->uid . '.' . $u->avatar;
			
			$r[] = $uobj;
		}
		
		return $r;
	}
	
	/**
	 * Generates an array with all new notifications
	 * 
	 * @param Notifications $n Notifications model
	 * @param int $uid userid
	 * @param int $time timestamp
	 * @param int $limit how many entries
	 */
	protected function notifications(Notifications $n, $uid, $time, $limit = 10) {
		$r = array();
		
		$result = $n->fetchAfter($uid, $time, $limit);
		while (($notification = $result->fetch_object()) != null) {
			$nobj = new stdClass();
			
			// if the readtime is empty the notifcations is unread
			$nobj->unread = $notification->readtime == '0000-00-00 00:00:00' ? 1 : 0;
			
			$nobj->inserttime = Printdate::get(strtotime($notification->inserttime), $this->view()->t); // Generate userfriendly date
			$nobj->readtime = $nobj->unread == 1 ? -1 : Printdate::get(strtotime($notification->readtime), $this->view()->t);
			
			// notifaction (html, javascript, everything is allowed)
			$nobj->text = $notification->text;
			
			$r[] = $nobj;
		}
		
		return $r;
	}
	
	/**
	 * Generates an array with all new news
	 * 
	 * @param News $n News model
	 * @param int $time Gimme all news after this
	 */
	protected function news(News $n, $time) {
		$r = array();
		
		$result = $n->fetchAfter($time);
		while (($notification = $result->fetch_object()) != null) {
			$nobj = new stdClass();
			
			// Userfriendly date
			$nobj->inserttime = Printdate::get(strtotime($notification->inserttime), $this->view()->t);
			
			// textbody (html, javascript, everything is allowed)
			$nobj->text = $notification->text;
			
			$r[] = $nobj;
		}
		
		return $r;
	}
	
	/**
	 * Called from clients
	 * 
	 * @param array $args not very interesting in this module
	 */
	public function run(array $args) {
		// random stuff
		$user = $this->getUser();
		$db = $this->getDb();
		
		// construct all needed models
		$users = new Users();
		$notifications = new Notifications();
		$news = new News();
		
		// i don't get this
		$time = $this->getRequest()->time - 10;
		
		if (!is_array($user) || $user['grade'] < 1) {
			// user is not logged (anonymous)
			echo json_encode($this->error($this->t->t('error/pleaselogin')));
			return;
		}
		
		// object for json
		$obj = new stdClass();
		
		$obj->notifications = $this->notifications($notifications, $user['uid'], $time);
		$obj->news = $this->news($news, $time);
		$obj->users = $this->users($users);
		
		// i don't get this again, but it really seems to work!!
		$obj->next = time() - 10;
		
		$obj->time = date('Y-m-d H:i:s', $time);
		
		// generate json and send to client
		echo json_encode($obj);
	}
	
}