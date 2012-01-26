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
		
		$url = ($this->_config->urlrewrite->base != '/' ? $this->_config->urlrewrite->base : '');
		$args = explode('/', substr(str_replace($url, '', $this->_request->getServer('REQUEST_URI')), 1, strlen($url)));

		$module = $this->findModule($args);
		$classname = $module . 'Module';
		
		// !isset($_SESSION['user']) && 
		if (isset($_COOKIE['remember'])) {
			// check rememberme cookie
			$users = new Users();
			
			$rowset = $users->findByCookie($_COOKIE['remember'], $this->_config->security->salt);
			if ($rowset->num_rows > 0) {
				// set user session
				$_SESSION['user'] = $rowset->fetch_array();
				
				// reload session
				$this->_request->reloadSession($_SESSION);
			}
		}
		
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