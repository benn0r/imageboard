<?php

/**
 * ImportModule
 * 
 * @author benn0r <benjamin@benn0r.ch>
 * @since 29102011
 * @version 29102011
 */

set_time_limit(5000);

class ImportModule extends Module
{
	
	public function run(array $args) {
		$db = $this->getDb();
		
		$dbold = new Database('localhost', 'root', '', 'board32');
		
		/*
		// ALT USERS
		$users = $dbold->select('
			SELECT * FROM board_users WHERE uid != 1 AND uid != 2 AND uid != 3
		');
		
		// NEU USERS
		while (($u = $users->fetch_object()) != null) {
			$db->insert('board_users', array(
				'uid' => $u->uid,
				'grade' => $u->grade,
				'status' => $u->status,
				'last_activity' => $u->last_activity,
				'online' => $u->online,
				'sid' => $u->sid,
				'username' => $u->username,
				'email' => $u->email,
				'birthday' => $u->birthday,
				'avatar' => $u->avatar,
				'password' => $u->password,
				'regtime' => $u->regtime,
				'board_perpage' => $u->board_perpage,
				'remote_addr' => $u->remote_addr,
			));
			
			if (file_exists('../board32/uploads/' . md5('av_' . $u->uid) . '.' . $u->avatar)) {
				copy('../board32/uploads/' . md5('av_' . $u->uid) . '.' . $u->avatar, 'avatars/' . $u->uid . '.' . $u->avatar);
			}
		}
		*/
		
		// ALT POSTS
		$posts = $dbold->select('
			SELECT * FROM board_posts
		');
		
		// NEU POSTS
		while (($p = $posts->fetch_object()) != null) {
			$media = $dbold->select('
				SELECT b.* FROM board_media2posts AS a
				LEFT JOIN board_media AS b ON a.mid = b.mid
				WHERE a.pid = ' . $p->pid . '
			');
			
			try {
				$db->insert('board_posts', array(
					'pid' => $p->pid,
					'ppid' => $p->ppid == 0 ? new Database_Expression('NULL') : $p->ppid,
					'replyto' => $p->replyto,
					'bid' => $p->bid,
					'uid' => $p->uid,
					'status' => $p->status,
					'updatetime' => $p->updatetime,
					'content' => $p->content,
				));
			} catch (Exception $ex) {
				echo 'pid:' . $p->pid;
			}
			
			while (($m = $media->fetch_object()) != null) {
				try {
					$db->insert('board_media', array(
						'pid' => $p->pid,
						'mid' => $m->mid,
						'status' => $m->status,
						'image' => $m->media,
						'inserttime' => $p->updatetime,
						'filename' => $m->type == 1 ? $m->media_1 : '',
						'extid' => $m->type == 2 ? $m->media_1 : '',
					));
				} catch (Exception $ex) {
					echo 'mid:' . $m->mid;
				}
				
				if (!is_dir('uploads/' . date('Ymd', strtotime($p->updatetime)))) {
					mkdir('uploads/' . date('Ymd', strtotime($p->updatetime)));
				}
				
				$folder = 'uploads/' . date('Ymd', strtotime($p->updatetime)) . '/' . $m->mid . '.' . $m->media;
				
				if (file_exists('../board32/uploads/' . md5('im_' . $m->mid) . '.' . $m->media)) {
					copy('../board32/uploads/' . md5('im_' . $m->mid) . '.' . $m->media, $folder);
				}
			}
		}
		
		
		/*$thumb = Module::init('Thumb', $this);
		
		$threads = $db->select('
			SELECT a.*,b.*,b.pid AS mpid,b.updatetime AS bmupdatetime
			FROM board_posts AS a
			LEFT JOIN board_media AS b ON a.pid = b.pid
			WHERE a.ppid IS NULL
			ORDER BY a.updatetime DESC
			LIMIT 0,15
		');
		
		$arr = array();
		while(($thread = $threads->fetch_object()) != null) {
			$key = array_push($arr, $thread);
			$media = new Media();
			
			$media->mid = $thread->mid;
			$media->name = $thread->name;
			$media->description = $thread->description;
			$media->published = strtotime($thread->published);
			$media->author = new Media_Uri($thread->author_name, $thread->author_uri);
			$media->source = new Media_Uri($thread->source_name, $thread->source_uri);
			$media->image = 'uploads/' . date('Ymd', strtotime($thread->updatetime)) . '/' . $thread->mid . '.' . $thread->media;
			
			$arr[$key - 1]->media = $media;
			
			echo $thumb->getThumbnail($media, 237, 283) . '<br />';
		}*/
		
		/*$media = $db->select('
			SELECT *
			FROM board_media
		');
		
		while(($m = $media->fetch_object()) != null) {
			if (!is_dir('uploads/' . date('Ymd', strtotime($m->updatetime)))) {
				mkdir('uploads/' . date('Ymd', strtotime($m->updatetime)));
			}
			rename('uploads/' . strtotime($m->updatetime) . '/' . md5('im_' . $m->mid) . '.' . $m->media,
				'uploads/' . date('Ymd', strtotime($m->updatetime)) . '/' . md5('im_' . $m->mid) . '.' . $m->media);
		}*/
		
		/*$threads = $db->select('
			SELECT a.pid,c.mid
			FROM board_posts AS a
			LEFT JOIN board_media2posts AS b ON a.pid = b.pid
			LEFT JOIN board_media AS c ON b.mid = c.mid
			WHERE a.ppid = 0 AND a.status = 1 AND c.status = 1
			ORDER BY a.pid DESC
		');
		
		while ($thread = $threads->fetch_object()) {
			echo $thread->pid . ' : ' . $thread->mid . '<br />';
			$db->update('board_media', array('pid' => $thread->pid), 'mid = ' . $thread->mid);
		}*/
	}
	
}