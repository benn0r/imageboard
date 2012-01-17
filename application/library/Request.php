<?php

/**
 * Request
 * 
 * Ersatz für die superglobalen von PHP
 * 
 * @author benn0r <benjamin@benn0r.ch>
 * @since 29102011
 * @version 29102011
 */
class Request
{
	
	protected $_server = array();
	protected $_query = array();
	protected $_post = array();
	protected $_files = array();
	protected $_cookie = array();
	protected $_session = array();
		
	public function __construct(array $server, array $query, array $post, array $files, array $cookie, array $session) {
		$this->_server = $server;
		$this->_query = $query;
		$this->_post = $post;
		$this->_files = $files;
		$this->_cookie = $cookie;
		$this->_session = $session;
	}
	
	public function url(Config $config) {
		$url = str_replace($config->urlrewrite->base, '', $this->get('REQUEST_URI'));
		$parts = explode('/', $url);
		
		return $parts;
	}
	
	public function __get($key) {
		if (isset($this->_query[$key])) {
			return $this->_query[$key];
		}
		if (isset($this->_post[$key])) {
			return $this->_post[$key];
		}
		return null;
	}
	
	public function get($key) {
		if (isset($this->_server[$key])) {
			return $this->_server[$key];
		}
		if (isset($this->_query[$key])) {
			return $this->_query[$key];
		}
		if (isset($this->_post[$key])) {
			return $this->_post[$key];
		}
		if (isset($this->_files[$key])) {
			return $this->_files[$key];
		}
		if (isset($this->_cookie[$key])) {
			return $this->_cookie[$key];
		}
		if (isset($this->_session[$key])) {
			return $this->_session[$key];
		}
		return false;
	}
	
	public function getServer($key) {
		if (isset($this->_server[$key])) {
			return $this->_server[$key];
		}
	}
	
	public function getSession($key = null) {
		if ($key) {
			if (isset($this->_session[$key])) {
				return $this->_session[$key];
			}
			return null;
		}
		return $this->_session;
	}
	
	/**
	 * Reload Session (needed after rememberme cookie)
	 */
	public function reloadSession($session) {
		$this->_session = $session;
	}
	
	public function isPost() {
		return count($this->_post) > 0;
	}
	
}