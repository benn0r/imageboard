<?php

/**
 * Language
 * 
 * Verwaltet alle Übersetzungen
 * 
 * @author benn0r <benjamin@benn0r.ch>
 * @since 29102011
 * @version 29102011
 */
class Language
{
	
	/**
	 * Schlüssel der Sprache (de, en, ..)
	 * 
	 * @var string
	 */
	protected $_key;
	
	/**
	 * Name der Sprache
	 * 
	 * @var string
	 */
	protected $_name;
	
	/**
	 * Dateiname der Translationdatei
	 * 
	 * @var string
	 */
	protected $_filename;
	
	/**
	 * Hier sind alle Übersetzungen gespeichert
	 * 
	 * @var array
	 */
	protected $_contents = array();
	
	/**
	 * Wenn eine Sprachdatei übergeben wurde wird diese
	 * mit _read() eingelesen
	 * 
	 * @param string $key Key der Sprache
	 * @param string $name Name der Sprache
	 * @param string $file Dateiname der Übersetzungsdatei
	 */
	public function __construct($key, $name, $file = null) {
		$this->_key = $key;
		$this->_name = $name;
		$this->_filename = $file;
		
		if ($file) {
			$this->_read($file);
		}
	}

	/**
	 * Liest eine Übersetzungsdatei ein
	 * 
	 * @param string $filename
	 * @return true
	 */
	protected function _read($filename) {
		$this->_contents = parse_ini_file($filename, true);
		
		// Alles okay nehm ich mal an hihi
		return true;
	}

	/**
	 * Akzeptiert Strings in der Form foo/bar. Weitere Möglichkeiten
	 * sind foo.bar oder foo_bar.
	 * In der Übersetzungsdatei sind die Übersetzungen in Kategorien
	 * eingeteilt:
	 * [foo]
	 * bar = bla
	 * 
	 * In $name werden auch Grossbuchstaben akzeptiert, Keys in den
	 * Übersetzungsfiles sollten aber alle klein geschrieben werden.
	 * 
	 * @param string $name
	 * @return string Übersetzung oder $name wenn keine Übersetzung
	 * 				  gefunden wurde
	 */
	public function translate($name) {
		$name = preg_split('/(\.|\/|_)/', $name);
		
		$arr = $this->_contents;
		foreach ($name as $key) {
			$key = strtolower($key);
			
			if (isset($arr[$key]) && is_array($arr[$key])) {
				// lets do recursion
				$arr = $arr[$key];
			} else {
				// translation found
				return $arr[$key];
			}
		}
		
		// no translation found, sorry
		return $name;
	}
	
	/**
	 * Returns the JSON representation of the
	 * translations
	 * 
	 * @return string a json encoded string on success
	 */
	public function json() {
		return json_encode($this->_contents);
	}
	
	/**
	 * Returns all translations
	 * 
	 * @return array array with translations
	 */
	public function getContents() {
		return $this->_contents;
	}
	
	/**
	 * Wrapper für translate()
	 * 
	 * @param string $name
	 * @return string
	 */
	public function _($name) {
		return $this->translate($name);
	}
	
	/**
	 * Wrapper für translate()
	 * 
	 * @param string $name
	 * @return string
	 */
	public function t($name) {
		return $this->translate($name);
	}
	
}