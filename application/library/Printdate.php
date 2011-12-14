<?php

/**
 * Printdate
 * 
 * @author benn0r <benjamin@benn0r.ch>
 * @since 29102011
 * @version 29102011
 */
class Printdate
{
	
	static public function get($time, Language $t) {
		if (date('Ymd', $time) == date('Ymd', time())) {
			return date($t->t('main/today'), $time);
		}
		if (date('Ymd', $time) == date('Ymd', time() - 86400)) {
			return date($t->t('main/yesterday'), $time);
		}
		
		return date($t->t('main/datetime'), $time);
	}

}