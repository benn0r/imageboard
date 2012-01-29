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

/**
 * RulesModule
 * 
 * @author benn0r <benjamin@benn0r.ch>
 * @since 2012/01/29
 * @version 2012/01/29
 */
class RulesModule extends Module
{
	
	public function run(array $args) {
		$file = 'application/config/rules_de.phtml';
		
		if (isset($_SESSION['user']) && $_SESSION['user']['language']) {
			if (!file_exists($file = 'application/config/rules_' . $_SESSION['user']['language'] . '.phtml')) {
				$file = 'application/config/rules_de.phtml';
			}
		}
		
		$this->view()->rules = $file;
		
		if (isset($_GET['ajax'])) {
			$this->render('rules', 'index');
		} else {
			$this->layout('rules', 'index');
		}
	}
	
}