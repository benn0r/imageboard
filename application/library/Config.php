<?php

/**
 * Config
 * 
 * Speicherplatz für alle Configurationen
 * 
 * @author benn0r <benjamin@benn0r.ch>
 * @since 29102011
 * @version 29102011
 */
class Config
{
	
	/**
	 * Speicherplatz der Konfigurationen
	 * 
	 * @var array
	 */
	protected $_config = array();
	
	/**
	 * Liest Array mit Konfigurationen ein. Mehrdimensionale Arrays
	 * werden in weitere Config Objekte zerlegt, das ermöglicht
	 * einfachen Zugriff auf die Configs via $config->foo->bar
	 * 
	 * @param array $config
	 */
	public function __construct(array $config) {
		foreach ($config as $cat => $val) {
			if (is_array($val)) {
				$this->_config[$cat] = new Config($val);
			} else {
				$this->_config[$cat] = $val;
			}
		}
	}
	
	/**
	 * Abfragen eines Wertes
	 * 
	 * @param string $key
	 * @return mixed
	 */
	public function __get($key) {
		if (array_key_exists($key, $this->_config)) {
			return $this->_config[$key];
		}
		
		// Key existiert nicht
		return false;
	}
	
	/**
	 * Gibt gesamte Config als Array zurück
	 * 
	 * @return array
	 */
	public function toArray() {
		$result = array();
		foreach ($this->_config as $cat => $val) {
			if ($val instanceof Config) {
				$result[$cat] = $val->toArray();
			} else {
				$result[$cat] = $val;
			}
		}
		
		return $result;
	}
	
}