<?php

/**
 * Media
 * 
 * @author benn0r <benjamin@benn0r.ch>
 * @since 29102011
 * @version 29102011
 */
class Media
{
	
	/**
	 * Ist das Thumbnail temporär oder nicht
	 * 
	 * @var bool
	 */
	public $temp = false;
	
	public $mid;

	/**
	 * Ein Titel für das Medium. z.b. der Titel
	 * eines Youtube Videos
	 * 
	 * @var string
	 */
	public $name;
	
	/**
	 * URL zu einem Bild das das Medium
	 * am besten darstellt. z.b. Youtube Video
	 * Thumbnail
	 * 
	 * @var string
	 */
	public $image;
	
	/**
	 * Eine Beschreibung des Medium. Z.b. die
	 * Beschreibung eines Youtubevideos
	 * 
	 * @var string
	 */
	public $description;
	
	/**
	 * Timestamp wann das Medium erstellt
	 * wurde
	 * 
	 * @var int
	 */
	public $published;
	
	/**
	 * Zum Medium passende Tags
	 * 
	 * @var array<string>
	 */
	public $tags = array();
	
	/**
	 * Objekt mit Link und Name des Autors
	 * 
	 * @var Media_Uri
	 */
	public $author;
	
	/**
	 * Objekt mit Name und Link der de Host Website
	 * des Medium (Nicht der Direktlink zum Medium!)
	 * 
	 * @var Media_Uri
	 */
	public $source;
	
	public $thumbnail;
	
	/**
	 * Plugin welches für dieses Medium
	 * zuständig ist
	 * 
	 * @var Media_Share_Plugin
	 */
	private $_plugin;
	
	/**
	 * Benötigt das Plugin des Mediums als
	 * Parameter
	 * 
	 * @param Media_Share_Plugin
	 */
	public function __construct(Media_Share_Plugin $plugin = null) {
		$this->_plugin = $plugin;	
	}
	
	public function getPlugin() {
		return $this->_plugin;
	}
	
	public function getFiletype() {
		$arr = explode('.', $this->image);
		
		return $arr[count($arr) - 1];
	}

}