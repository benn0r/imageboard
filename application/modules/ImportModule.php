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
		$dbold = new Database('luukasc.mysql.db.internal', 'luukasc_4fag', '4fag@mysql@5400', 'luukasc_4fag');
		
//  		$users = array('1125', '1126', '1127', '1128');
		
// 		for ($i = 0; $i < 100; $i++) {
// 			$date = '2011-12-0' . mt_rand(1, 9) . ' 00:00:00';
// 			$db->insert('board_posts', array('uid' => $users[mt_rand(0, 3)], 'content' => 'test', 'ppid' => 77, 'updatetime' => $date));
// 		}
		
		// ALT USERS
		/*$users = $dbold->select('
			SELECT * FROM board_users
		');
		
		// NEU USERS
		while (($u = $users->fetch_object()) != null) {
			if (file_exists('../uploads/' . md5('av_' . $u->uid) . '.' . $u->avatar)) {
				copy('../uploads/' . md5('av_' . $u->uid) . '.' . $u->avatar, 
					'uploads/avatars/' . $u->uid . '.' . $u->avatar);
			} else {
				$u->avatar = 'png';
				copy('images/avatar.png', 
					'uploads/avatars/' . $u->uid . '.' . $u->avatar);
			}
			
			$db->insert('board_users', array(
				'uid' => $u->uid,
				'grade' => $u->grade,
				'status' => $u->status,
				'last_activity' => $u->last_activity,
				'online' => $u->online,
				'sid' => !$u->sid ? new Database_Expression('NULL') : $u->sid,
				'username' => $u->username,
				'email' => $u->email,
				'birthday' => $u->birthday,
				'avatar' => $u->avatar,
				'password' => $u->password,
				'regtime' => $u->regtime,
				'board_perpage' => $u->board_perpage,
				'remote_addr' => $u->remote_addr,
			));
		}
		
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
					'uid' => $p->uid == 3 ? new Database_Expression('NULL') : $p->uid,
					'status' => $p->status,
					'updatetime' => $p->updatetime,
					'content' => utf8_decode($p->content),
				));
			} catch (Exception $ex) {
				print_r($ex);
				echo 'pid:' . $p->pid;
			}
			
			try {
				$db->insert('board_posts2tags', array(
					'pid' => $p->pid,
					'tid' => $p->bid,
					'uid' => $p->uid == 3 ? new Database_Expression('NULL') : $p->uid,
				));
			} catch (Exception $ex) { }
			
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
						'type' => $m->type == 2 ? 4 : $m->type,
					));
				} catch (Exception $ex) {
					echo 'mid:' . $m->mid;
				}
				
				$ratings = $dbold->select('
					SELECT * FROM board_mediaratings
					WHERE mid = "' . $m->mid . '"
				');
				while (($rating = $ratings->fetch_object()) != null) {
					try {
						$db->insert('board_mediaratings', array(
							'mid' => $rating->mid,
							'uid' => $rating->uid,
							'updatetime' => $rating->updatetime,
							'rating' => $rating->rating >= 3 ? 1 : 0,
						));
					} catch (Exception $ex) {
						echo 'mediarating:' . $m->mid . '/' . $rating->uid;
					}
				}
				
				if (!is_dir('uploads/' . date('Ymd', strtotime($p->updatetime)))) {
					mkdir('uploads/' . date('Ymd', strtotime($p->updatetime)));
				}
				
				$folder = 'uploads/' . date('Ymd', strtotime($p->updatetime)) . '/' . $m->mid . '.' . $m->media;
				
				if (file_exists('../uploads/' . md5('im_' . $m->mid) . '.' . $m->media) && 
						!file_exists($folder)) {
					rename('../uploads/' . md5('im_' . $m->mid) . '.' . $m->media, $folder);
				}
			}
		}*/
		
		
		$visits = $dbold->select('
			SELECT * FROM board_postvisits
		');
			
		while (($visit = $visits->fetch_object()) != null) {
			try {
				$db->insert('board_postvisits', array(
					'pid' => $visit->pid,
					'uid' => $visit->uid == 3 || $visit->uid == 0 ? new Database_Expression('NULL') : $visit->uid,
					'visittime' => $visit->visittime,
					'remote_addr' => $visit->remote_addr,
					'http_user_agent' => $visit->http_user_agent,
				));
			} catch (Exception $ex) { }
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