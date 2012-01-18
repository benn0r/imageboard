<?php

/**
 * Copyright (c) 2012 benn0r <benjamin@benn0r.ch>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

error_reporting(E_ALL);

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

header('Content-Type: text/html; charset=utf-8');

$application = new Application();
$application->bootstrap()
			->helper(new Helper($application))
			->run('application/config/application.ini');