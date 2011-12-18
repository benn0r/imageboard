<?php

/**
 * Language
 * 
 * Manages all translations
 * 
 * @author benn0r <benjamin@benn0r.ch>
 * @since 2011/10/29
 * @version 2011/12/16
 */
class Language
{
	
	/**
	 * Language key (de, en, ..). Used for the
	 * languagefiles
	 * 
	 * @var string
	 */
	protected $_key;
	
	/**
	 * Languagename
	 * 
	 * @var string
	 */
	protected $_name;
	
	/**
	 * Filename of the translationfile. Must be
	 * relative or absolute path
	 * 
	 * @var string
	 */
	protected $_filename;
	
	/**
	 * Array with all Translations
	 * 
	 * @var array
	 */
	protected $_contents = array();
	
	/**
	 * If $file parameter is a file, we try to read
	 * the file
	 * 
	 * @param string $key Languagekey (de, en...)
	 * @param string $name Languagename
	 * @param string $file Translationfilename
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
	 * Reads an translationfile with parse_ini_file.
	 * Learn more about this at
	 * http://php.net/manual/de/function.parse-ini-file.php
	 * 
	 * @param string $filename relative or absolute path
	 * @return true it always works, maybe
	 */
	protected function _read($filename) {
		$this->_contents = parse_ini_file($filename, true);
		
		// yes, everything is okay
		return true;
	}

	/**
	 * Samples for calling this method:
	 * foo/bar
	 * foo.bar
	 * foo_bar
	 * 
	 * First string is the group. Learn more about this at
	 * http://php.net/manual/de/function.parse-ini-file.php
	 * 
	 * This method is case insensitivity. Keys and groupnames
	 * in the language better be lower case, thanks.
	 * 
	 * @param string $name
	 * @return string Translation, returns $name if no translation found
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
	 * Cool wrapper for translate()
	 * 
	 * @param string $name
	 * @return string
	 */
	public function _($name) {
		return $this->translate($name);
	}
	
	/**
	 * Cool wrapper for translate(), i usually work 
	 * with this method
	 * 
	 * @param string $name
	 * @return string
	 */
	public function t($name) {
		return $this->translate($name);
	}
	
}