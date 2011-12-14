<?php

/**
 * BoardModule
 * 
 * @author benn0r <benjamin@benn0r.ch>
 * @since 29102011
 * @version 29102011
 */
class BoardModule extends Module
{
	
	public function run(array $args) {
		$user = $this->getUser();
		$db = $this->getDb();
		$view = $this->view();
		
		// Laden des Thumb Module
		$thumb = Module::init('Thumb', $this);
		
		$threads = $db->select('
			SELECT a.*,b.*,b.pid AS mpid,b.updatetime AS bmupdatetime,c.*
			FROM board_posts AS a
			LEFT JOIN board_media AS b ON a.pid = b.pid
			LEFT JOIN board_users AS c ON a.uid = c.uid
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
			
			$media->thumbnail = $thumb->getThumbnail($media, 237, 283);
			
			$arr[$key - 1]->media = $media;
		}
		
		$view->threads = $arr;
		
		// Das richtige View ausgeben
		switch ($user['board']) {
			case 1:
				//$this->render('board', 'tree');
				$this->render('main', 'header');
				break;
			case 2:
				$this->render('board', 'default');
				break;
		}
	}
	
}