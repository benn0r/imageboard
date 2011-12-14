<?php

/**
 * NotificationsModule
 * 
 * Wird in regelmässigen Abständen vom Client aufgerufen und
 * liefert ausser den Notifications auch die Einträge für die
 * "Aktive Nutzer Liste" und die "News".
 * 
 * Notifications und News werden immer nur die neusten angezeigt
 * (anhand des übergebenen Timestamps) wobei bei den Benutzern
 * immer alle aktiven (online = 1) zurückgegeben werden
 * Notifications, News und Users können auf ein Maximum beschränkt
 * werden.
 * 
 * @author benn0r <benjamin@benn0r.ch>
 * @since 10122011
 * @version 10122011
 */
class NotificationsModule extends Module
{
	
	/**
	 * Bei einem Fehler generiert diese Methode
	 * eine JSON-Gerechte Antwort die vom Client
	 * ausgewertet werden kann
	 * 
	 * @param int $error
	 */
	protected function error($error) {
		$obj = new stdClass();
		$obj->error = 1;
		$obj->message = $error;
		
		return $obj;
	}
	
	/**
	 * Lädt alle aktiven Benutzer
	 * 
	 * @param Users $users
	 */
	protected function users(Users $users) {
		$r = array();
		$active = $users->active();
		
		while (($u = $active->fetch_object()) != null) {
			$uobj = new stdClass();
			$uobj->uid = $u->uid;
			$uobj->username = $u->username;
			
			// URL zum Avatar wird direkt serverseitig ausgegeben, damit das Javascript welches
			// die Antwort auswertet nicht zu viel Arbeit hat
			$uobj->avatar = $this->view()->baseUrl() . $this->getConfig()->paths->avatars . '/' . $u->uid . '.' . $u->avatar;
			
			$r[] = $uobj;
		}
		
		return $r;
	}
	
	/**
	 * Lädt alle Notifications für den angegebenen Nutzer
	 * 
	 * @param Notifications $n
	 * @param int $uid ID des Nutzers
	 * @param int $time Alle Einträge ab diesem Timestamp
	 * @param int $limit Wie viele Einträge
	 */
	protected function notifications(Notifications $n, $uid, $time, $limit = 10) {
		$r = array();
		
		$result = $n->fetchAfter($uid, $time, $limit);
		while (($notification = $result->fetch_object()) != null) {
			$nobj = new stdClass();
			
			// Wenn noch keine Zeit angegeben wurde ist der Beitrag ungelesen
			$nobj->unread = $notification->readtime == '0000-00-00 00:00:00' ? 1 : 0;
			
			// Serverseitig das lesbare Datum ausgeben
			$nobj->inserttime = Printdate::get(strtotime($notification->inserttime), $this->view()->t);
			
			// Readtime geben wir mit wenn es eine gibt
			$nobj->readtime = $nobj->unread == 1 ? -1 : Printdate::get(strtotime($notification->readtime), $this->view()->t);
			
			// Der Text der Nachricht (Notifications werden nur maschinell
			// eingetragen, dürfen also HTML etc. enthalten)
			$nobj->text = $notification->text;
			
			$r[] = $nobj;
		}
		
		return $r;
	}
	
	/**
	 * Lädt alle News ab einem bestimmten Timestamp
	 * 
	 * @param News $n
	 * @param int $time Alle News ab diesem Timestamp
	 */
	protected function news(News $n, $time) {
		$r = array();
		
		$result = $n->fetchAfter($time);
		while (($notification = $result->fetch_object()) != null) {
			$nobj = new stdClass();
			
			// Serverseitig das lesbare Datum ausgeben
			$nobj->inserttime = Printdate::get(strtotime($notification->inserttime), $this->view()->t);
			
			// News werden nur von authorisierten Personen eingetragen und 
			// dürfen deshalb HTML, Javascript etc. beinhalten
			$nobj->text = $notification->text;
			
			$r[] = $nobj;
		}
		
		return $r;
	}
	
	/**
	 * Hauptmethode von Notifications
	 * 
	 * @param array $args
	 */
	public function run(array $args) {
		// Standard Zeugs reinladen
		$user = $this->getUser();
		$db = $this->getDb();
		
		// Alle Models laden
		$users = new Users();
		$notifications = new Notifications();
		$news = new News();
		
		// Zeitstempfel manipulieren
		$time = $this->getRequest()->time - 10;
		
		if (!is_array($user) || $user['grade'] < 1) {
			// User hat keine Rechte oder ist nicht eingeloggt
			echo json_encode($this->error($this->t->t('error/pleaselogin')));
			return;
		}
		
		$obj = new stdClass();
		
		// Die Notifications seit dem angegeben Timestamp
		$obj->notifications = $this->notifications($notifications, $user['uid'], $time);
		
		// Die News seit dem angegeben Timestamp
		$obj->news = $this->news($news, $time);
		
		// Hier werden immer _alle_ aktiven User geladen
		$obj->users = $this->users($users);
		
		// Timestamp welcher von jquery für die nächste Abfrage verwendet
		// werden soll
		$obj->next = time() - 10;
		
		$obj->time = date('Y-m-d H:i:s', $time);
		
		// $obj zu JSON umwandeln und an den anfragenden Client schicken
		echo json_encode($obj);
	}
	
}