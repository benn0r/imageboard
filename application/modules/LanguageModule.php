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
 * LanguageModule
 * 
 * @author benn0r <benjamin@benn0r.ch>
 * @since 2012/01/13
 * @version 2012/01/13
 */
class LanguageModule extends Module
{
	
	public function run(array $args) {
		
		header('Content-Type: text/javascript');
				
		// print all translations for javascript
		// json_encode from php does not work in internet explorer? (tested in ie9)
		$o = 'var t = {';
		foreach ($this->getLanguage()->getContents() as $key => $arr) {
			$o .= '"' . $key . '":{';
			foreach ($arr as $key => $txt) {
				$o .= '"' . $key . '":"' . str_replace('\\', '\\\\', $txt) . '",';
			}
			$o = substr($o, 0, -1) . '},';
		}
		
		echo substr($o, 0, -1) . '};';
		
	}
	
}