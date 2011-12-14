<?php

/**
 * IndexModule
 * 
 * @author benn0r <benjamin@benn0r.ch>
 * @since 29102011
 * @version 29102011
 */
abstract class Application_Helper
{
	
	protected $_application;
	
	public function __construct(Application $application) {
		$this->_application = $application;
	}
	
	abstract public function getModule($module);
	
}