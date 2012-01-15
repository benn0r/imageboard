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
	 * @param int $nid last notification id
	 * @param int $limit how many entries
	 */
	protected function notifications(Notifications $n, $uid, $nid, $limit = 10) {
		$r = array();
		
		$result = $n->fetchAfter($uid, $nid, $limit);
		while (($notification = $result->fetch_object()) != null) {
			$nobj = new stdClass();
			
			$nobj->nid = $notification->nid;
			
			// if the readtime is empty the notifcations is unread
			$nobj->unread = $notification->readtime == '0000-00-00 00:00:00' ? 1 : 0;
			
			$nobj->inserttime = Printdate::get(strtotime($notification->inserttime), $this->view()->t); // Generate userfriendly date
			$nobj->readtime = $nobj->unread == 1 ? -1 : Printdate::get(strtotime($notification->readtime), $this->view()->t);
			
			// notifaction (html, javascript, everything is allowed)
			$nobj->text = $notification->text;
			
			$posts = new Posts();
			$post = $posts->find($notification->pid);
			$rowset = $posts->fetchMedia($notification->thread);
			
			// load module for generating thumbnails
			$thumb = Module::init('Thumb', $this);
			
			$allmedia = array();
			while (($c = $rowset->fetch_object()) != null) {
				$media = new Media();
			
				$media->mid = $c->mid;
				$media->image = 'uploads/' . date('Ymd', strtotime($c->inserttime)) . '/' . $c->mid . '.' . $c->image;
			
				$media->thumbnail = $thumb->getThumbnail($media, 50, 50);
			
				$allmedia[] = $media;
			}
			
			$nobj->thread = $this->view()->baseUrl() . 'thread/' . $notification->thread;
			$nobj->thumbnail = $this->view()->baseUrl() . $allmedia[0]->thumbnail;
			$nobj->content = strlen($post->content) > 70 ? substr($post->content, 0, 67) . '...' : $post->content;
			
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
	
	public function add($post, $thread, $comments) {
		$table = new Notifications();
		$t = $this->getLanguage();
		$u = $this->view()->baseurl();
		
		$informed = array();
		
		if ($post->uid != $thread->uid) {
			// thread owner
			$table->insert(array(
					'uid' => $thread->uid,
					'status' => 1,
					'pid' => $post->pid,
					'thread' => $thread->pid,
					'text' => sprintf($t->t('notification/yourthread'), $u . 'user/' . $post->uid, $post->username, $u . 'thread/' . $thread->pid),
			));
		}
		
		$informed[] = $thread->uid;
		
		foreach ($comments as $comment) {
			if (!in_array($comment->uid, $informed) && $post->uid != $comment->uid) {
				if ($thread->uid == $post->uid) {
					$text = sprintf($t->t('notification/histhread'),
							$u . 'user/' . $post->uid,
							$post->username,
							$u . 'thread/' . $thread->pid);
				} else {
					$text = sprintf($t->t('notification/thread'),
							$u . 'user/' . $post->uid,
							$post->username,
							$u . 'user/' . $thread->uid,
							$thread->username,
							$u . 'thread/' . $thread->pid);
				}
				
				$table->insert(array(
						'uid' => $comment->uid,
						'status' => 1,
						'pid' => $post->pid,
						'thread' => $thread->pid,
						'text' => $text,
				));
				
				$informed[] = $comment->uid;
			}
		}
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
		$time = $this->getRequest()->nid;
		
		if (!is_array($user) || $user['grade'] < 1) {
			// user is not logged (anonymous)
			echo json_encode($this->error($this->t->t('error/pleaselogin')));
			return;
		}
		
		// object for json
		$obj = new stdClass();
		
		$obj->notifications = $this->notifications($notifications, $user['uid'], $time);
		//$obj->news = $this->news($news, $time);
		$obj->users = $this->users($users);
		
		// i don't get this again, but it really seems to work!!
		$obj->next = time() - 10;
		
		$obj->time = date('Y-m-d H:i:s', $time);
		$obj->lastn = $obj->notifications[count($obj->notifications) - 1]->nid;
		
		// generate json and send to client
		echo json_encode($obj);
	}
	
}