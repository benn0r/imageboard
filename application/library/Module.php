<?php

/**
 * Module
 * 
 * Basis für alle Module
 * 
 * @author benn0r <benjamin@benn0r.ch>
 * @since 29102011
 * @version 29102011
 */
abstract class Module
{
	
	protected $_request;
	
	protected $_db;
	
	protected $_lang;
	
	protected $_view;
	
	protected $_config;
	
	public function __construct(Request $request, Database $db, Language $lang, View $view, Config $cfg) {
		$this->_request = $request;
		$this->_db = $db;
		$this->_lang = $lang;
		$this->_view = $view;
		$this->_config = $cfg;
		
		// View mit Objekten füllen
		$view->t = $this->_lang;
		$view->request = $this->_request;
		$view->user = $this->getRequest()->getSession('user');
	}
	
	/**
	 * Erstellt eine neue Instanz eines Modules. Wird normalerweise 
	 * dafür verwendet um auf Methoden eines Moduls zuzugreifen aus
	 * einem anderen Modul heraus. Um dem neuen Modul Request und
	 * Database zu übergeben brauchen wir ein Parent Modul bei dem
	 * wir diese Objekte holen.
	 * 
	 * @param string $name Name des Moduls
	 * @param Module $parent Parent mit Request und Database
	 */
	static public function init($name, Module $parent) {
		$classname = $name . 'Module';
		$module = new $classname($parent->getRequest(), $parent->getDb(), $parent->getLanguage(), $parent->view(), $parent->_config);
		
		return $module;
	}
	
	protected function view() {
		return $this->_view;
	}
	
	protected function render($module, $action) {
		return $this->view()->render($module, $action);
	}
	
	protected function layout($module, $action, $layout = 'layout') {
		$view = $this->view();
		
		$view->module = $module;
		$view->action = $action;
		
		return $this->render('main', $layout);
	}
	
	protected function getRequest() {
		return $this->_request;
	}
	
	protected function getDb() {
		return $this->_db;
	}
	
	protected function getLanguage() {
		return $this->_lang;
	}
	
	protected function getConfig() {
		return $this->_config;
	}
	
	protected function getUser() {
		return $this->getRequest()->getSession('user');
	}

}