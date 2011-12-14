<?php

/**
 * Language
 * 
 * Verwaltet alle �bersetzungen
 * 
 * @author benn0r <benjamin@benn0r.ch>
 * @since 29102011
 * @version 29102011
 */
class Language
{
	
	/**
	 * Schl�ssel der Sprache (de, en, ..)
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
	 * Hier sind alle �bersetzungen gespeichert
	 * 
	 * @var array
	 */
	protected $_contents = array();
	
	/**
	 * Wenn eine Sprachdatei �bergeben wurde wird diese
	 * mit _read() eingelesen
	 * 
	 * @param string $key Key der Sprache
	 * @param string $name Name der Sprache
	 * @param string $file Dateiname der �bersetzungsdatei
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
	 * Liest eine �bersetzungsdatei ein
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
	 * Akzeptiert Strings in der Form foo/bar. Weitere M�glichkeiten
	 * sind foo.bar oder foo_bar.
	 * In der �bersetzungsdatei sind die �bersetzungen in Kategorien
	 * eingeteilt:
	 * [foo]
	 * bar = bla
	 * 
	 * In $name werden auch Grossbuchstaben akzeptiert, Keys in den
	 * �bersetzungsfiles sollten aber alle klein geschrieben werden.
	 * 
	 * @param string $name
	 * @return string �bersetzung oder $name wenn keine �bersetzung
	 * 				  gefunden wurde
	 */
	public function translate($name) {
		$name = preg_split('/(\.|\/|_)/', $name);
		
		$arr = $this->_contents;
		foreach ($name as $key) {
			$key = strtolower($key);
			
			if (isset($arr[$key]) && is_array($arr[$key])) {
				// Ist der Key ein Array dann gehen wir eine Ebene tiefer
				$arr = $arr[$key];
			} else {
				// Fertig, die �bersetzung wurde gefunden
				return $arr[$key];
			}
		}
		
		// Leider nix gefunden, sorry man
		return $name;
	}
	
	/**
	 * Wrapper f�r translate()
	 * 
	 * @param string $name
	 * @return string
	 */
	public function _($name) {
		return $this->translate($name);
	}
	
	/**
	 * Wrapper f�r translate()
	 * 
	 * @param string $name
	 * @return string
	 */
	public function t($name) {
		return $this->translate($name);
	}
	
}