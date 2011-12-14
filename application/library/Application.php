<?php

/**
 * Application
 * 
 * @author benn0r <benjamin@benn0r.ch>
 * @since 29102011
 * @version 29102011
 */
class Application
{
	
	protected $_config;
	
	protected $_request;
	
	protected $_db;
	
	protected $_lang;
	
	protected $_helper;
	
	public function bootstrap() {
		return $this;
	}
	
	public function helper(Application_Helper $helper) {
		$this->_helper = $helper;
		
		return $this;
	}
	
	public function run($configfile) {
		header('Content-Type: text/html; charset=utf-8');
		
		$this->_config = $config = new Config(parse_ini_file($configfile, true));
		
		$this->_request = new Request($_SERVER, $_GET, $_POST, $_FILES, $_COOKIE, $_SESSION);
		
		$dblogin = $this->_config->database;
		$this->_db = new Database($dblogin->host, $dblogin->username, $dblogin->passwd, $dblogin->dbname);
		
		Model::setDefaultDb($this->_db);
		
		$this->_lang = new Language('de', $config->language->names->de, $config->language->files->de);
		
		$args = explode('/', str_replace($this->_config->urlrewrite->base, '',
			$this->_request->getServer('REQUEST_URI')));
		
		$module = $this->findModule($args);
		$classname = $module . 'Module';
		
		$view = new View($this->_request, $this->_config);
		
		$obj = new $classname($this->_request, $this->_db, $this->_lang, $view, $this->_config);
		$obj->run($args);
	}
	
	public function findModule(array $uri) {
		
		if ($this->_helper) {
			return $this->_helper->getModule($uri[0]);
		}
		
		return $uri[0];
	}
	
	public function getDb() {
		return $this->_db;
	}
	
}

?>