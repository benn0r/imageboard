<?php

/**
 * View
 * 
 * @author benn0r <benjamin@benn0r.ch>
 * @since 29102011
 * @version 29102011
 */
class View
{
	
	protected $_data = array();
	
	protected $_request;
	
	protected $_config;
	
	public function __construct(Request $r, Config $c) {
		$this->_request = $r;
		$this->_config = $c;
	}
	
	public function render($module, $action = null) {
		if (!$action) {
			require $module;
		} else {
			require $module . '/' . $action . '.phtml';
		}
	}
	
	public function baseUrl() {
		$r = $this->_request;
		$c = $this->_config;
		
		$url = 'http';
 		if ($r->getServer('HTTPS') == 'on') {
 			$url .= 's';
 		}
 		$url .= '://' . $r->getServer('SERVER_NAME') . $c->urlrewrite->base;
 		
 		return $url;
	}
	
	public function getConfig() {
		return $this->_config;
	}
	
	public function __set($key, $value) {
		$this->_data[$key] = $value;
	}
	
	public function __get($key) {
		if (array_key_exists($key, $this->_data)) {
			return $this->_data[$key];
		}
		return null;
	}
	
}