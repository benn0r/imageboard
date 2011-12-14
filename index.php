<?php

set_include_path(
	'application/' . PATH_SEPARATOR .
	'application/library/' . PATH_SEPARATOR .
	'application/modules/' . PATH_SEPARATOR .
	'application/models/' . PATH_SEPARATOR .
	'application/views/' . PATH_SEPARATOR .
	get_include_path()
);

session_start();

function __autoload($classname) {
	require_once str_replace('_', '/', $classname) . '.php';
}

$application = new Application();
$application->bootstrap()
			->helper(new Helper($application))
			->run('application/config/application.ini');