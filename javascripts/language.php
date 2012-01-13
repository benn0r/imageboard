<?php

require_once '../../application/library/Language.php';

$t = new Language('de', '', '../../applications/languages/de.ini');

// print all translations for javascript
// json_encode from php does not work in internet explorer? (tested in ie9)
$o = 'var t = {';
foreach ($t->getContents() as $key => $arr) {
	$o .= '"' . $key . '":{';
	foreach ($arr as $key => $txt) {
		$o .= '"' . $key . '":"' . str_replace('\\', '\\\\', $txt) . '",';
	}
	$o = substr($o, 0, -1) . '},';
}

echo substr($o, 0, -1) . '};';
?>